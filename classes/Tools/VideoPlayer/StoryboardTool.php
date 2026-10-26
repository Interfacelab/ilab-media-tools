<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer;

use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\VideoEncodingTool;
use MediaCloud\Plugin\Tools\Video\VideoEncodingToolSettings;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Prefixer;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\Exception\GuzzleException;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\arrayPath;

class StoryboardTool {
	public function __construct() {
		add_filter('template_include', [$this, 'handleWebhook']);
		add_action('media-cloud/storage/uploaded-attachment', [$this, 'handleUpload'], 1000, 3);
		add_action('media-cloud/storage/direct-uploaded-attachment', [$this, 'handleDirectUpload'], 1000, 2);
	}

	public function handleUpload($postId, $file, $meta) {
		$this->handleDirectUpload($postId, $meta);
	}

	public function handleDirectUpload($postId, $meta) {
		$type = arrayPath($meta, 'type', null);
		if (empty($type)) {
			$type = arrayPath($meta, 'mime_type', null);
			if (empty($type)) {
				$type = arrayPath($meta, 's3/mime-type', null);

				if (empty($type)) {
					return;
				}
			}
		}

		if ((strpos($type, 'video') !== 0) || !isset($meta['s3'])) {
			return;
		}

		try {
			$this->generateStoryboard($postId);
		} catch (\Exception $ex) {
			Logger::error($ex->getMessage(), [], __METHOD__, __LINE__);
		}
	}

	public function handleWebhook($template) {
		if (strpos($_SERVER['REQUEST_URI'], '/__mediacloud/services/storyboard') === 0) {
			$authHeader = arrayPath($_SERVER, 'HTTP_AUTHORIZATION', null);
			$vttJSON = arrayPath($_SERVER, 'HTTP_X_MEDIA_CLOUD_VTT', null);
			$propsJSON = arrayPath($_SERVER, 'HTTP_X_MEDIA_CLOUD_PROPS', null);
			$attachmentId = arrayPath($_SERVER, 'HTTP_X_MEDIA_CLOUD_AID', null);
			$time = arrayPath($_SERVER, 'HTTP_X_MEDIA_CLOUD_TIME', null);

			if (anyEmpty($authHeader, $vttJSON, $propsJSON, $attachmentId, $time)) {
				Logger::error("Storyboard webhook sent bogus headers.", [], __METHOD__, __LINE__);
				wp_send_json(['status' => 'Invalid headers.'], 400);
			}

			$post = get_post($attachmentId);
			if (is_wp_error($post) || empty($post)) {
				Logger::error("Invalid post ID", [], __METHOD__, __LINE__);
				wp_send_json(['status' => 'Invalid post ID.'], 400);
			}

			$authParts = explode(" ", $authHeader);
			if (count($authParts) !== 2) {
				Logger::error("Invalid auth.", [], __METHOD__, __LINE__);
				wp_send_json(['status' => 'Not authorized'], 401);
			}

			/** @var \Freemius $media_cloud_licensing */
			global $media_cloud_licensing;
			$license_id = $media_cloud_licensing->_get_license()->id;
			$install_id = $media_cloud_licensing->get_site()->id;
			$site_private_key = $media_cloud_licensing->get_site()->secret_key;

			$auth = $authParts[1];
			$verification = base64_encode(hash('sha512', $attachmentId.'|'.$license_id.'|'.$install_id.'|'.$time.'|'.$site_private_key));
			if ($auth !== $verification) {
				Logger::error("Invalid auth signature.", [], __METHOD__, __LINE__);
				wp_send_json(['status' => 'Signatures do not match'], 401);
			}

			$props = json_decode($propsJSON, true);
			$vtt = json_decode(str_replace("\\\"", "\"", $vttJSON), true);

			$uploadDirInfo = wp_get_upload_dir();

			add_filter('big_image_size_threshold', '__return_false');

			if (ToolsManager::instance()->toolEnabled('storage')) {
				Prefixer::setType('image/jpeg');
				$prefix = StorageToolSettings::prefix();

				$uploadDir = trailingslashit($uploadDirInfo['basedir']) . $prefix;
				if (!file_exists($uploadDir)) {
					@mkdir($uploadDir, 0777, true);
				}
			} else {
				$uploadDir = trailingslashit($uploadDirInfo['basedir']);
			}

			if (!file_exists($uploadDir)) {
				@mkdir($uploadDir, 0777, true);
			}

			$filePath = trailingslashit($uploadDir) . $attachmentId . '-storyboard.jpg';
			file_put_contents($filePath, stream_get_contents(fopen('php://input', 'r')));

			if(!file_exists($filePath)) {
				Logger::error("Could not save storyboard.", [], __METHOD__, __LINE__);
				wp_send_json(['status' => 'Could not save storyboard'], 500);
			}

			$thumbId = wp_insert_attachment([
				'post_parent' => $attachmentId,
				'post_mime_type' => 'image/jpeg',
				'post_title' => $post->post_title . ' Storyboard',
				'post_content' => '',
				'post_status' => 'inherit'
			]);

			Logger::info("Created storyboard attachment {$thumbId}.", [], __METHOD__, __LINE__);

			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$thumbAttachmentMeta = wp_generate_attachment_metadata($thumbId, $filePath);
			update_post_meta($thumbId, '_wp_attached_file', $thumbAttachmentMeta['file']);
			wp_update_attachment_metadata($thumbId, $thumbAttachmentMeta);

			update_post_meta($attachmentId, 'storyboard', $thumbId);

			$storyboardUrl = wp_get_attachment_image_url($thumbId, 'full');

			$finalVTT = str_replace('%IMAGEURL%', $storyboardUrl, implode("\n", $vtt));

			$uploadedFile = wp_upload_bits( $attachmentId . '-storyboard.vtt', null, $finalVTT);
			update_post_meta($attachmentId, 'storyboard_vtt', $uploadedFile['url']);

			wp_send_json(['status' => 'ok'], 200);
		}

		return $template;
	}

	/**
	 * Generates a storyboard for a given video attachment
	 * @param $attachmentId
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function generateStoryboard($attachmentId) {
		$input = wp_get_attachment_url($attachmentId);
		$path = parse_url($input, PHP_URL_PATH);
		if (!in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'm4v'])) {
			return false;
		}

		/** @var \Freemius $media_cloud_licensing */
		global $media_cloud_licensing;
		$license_id = $media_cloud_licensing->_get_license()->id;
		$install_id = $media_cloud_licensing->get_site()->id;
		$site_private_key = $media_cloud_licensing->get_site()->secret_key;

		$datetime = new \DateTime();
		$ts = $datetime->format(\DateTime::ATOM);

		$client = new Client();
		try {
			$host = trailingslashit(apply_filters('media-cloud/video-encoding/storyboard-webhook-host', home_url('/')));

//			$res = $client->post('http://host.docker.internal:3000', [
			$client->post('https://storyboarder.mediacloud.press', [
				RequestOptions::HEADERS => [
					'Authorization' => 'Bearer '.base64_encode(hash('sha512', $site_private_key . '|' . $input . '|' . $ts)),
					'Content-Type' => 'application/json'
				],
				RequestOptions::BODY => json_encode([
					'task' => 'storyboard',
					'url' => $input,
					'callback' => $host.'__mediacloud/services/storyboard',
					'encoder' => ToolsManager::instance()->toolEnvEnabled('video-encoding') ? VideoEncodingToolSettings::driver() ?? 'none' : 'none',
					'site' => home_url(),
					'aid' => "$attachmentId",
					'lid' => "$license_id",
					'iid' => "$install_id",
					'ts' => $ts
				]),
				RequestOptions::CONNECT_TIMEOUT => 0,
				RequestOptions::READ_TIMEOUT => 150,
				RequestOptions::TIMEOUT => 150,
			]);

			return true;
		} catch (GuzzleException $ex) {
			Logger::error($ex->getMessage(), [], __METHOD__, __LINE__);
			throw $ex;
		} catch(\Exception $ex) {
			Logger::error($ex->getMessage(), [], __METHOD__, __LINE__);
			throw $ex;
		}
	}
}