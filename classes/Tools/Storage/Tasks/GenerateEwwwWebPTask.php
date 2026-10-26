<?php

namespace MediaCloud\Plugin\Tools\Storage\Tasks;

use MediaCloud\Plugin\Tasks\AttachmentTask;
use MediaCloud\Plugin\Tools\ImageSizes\ImageSizePrivacy;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\postIdExists;
use function \MediaCloud\Plugin\Utilities\arrayPath;

class GenerateEwwwWebPTask extends AttachmentTask {

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'generate-ewww-webp';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Generate EWWW WebP';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Generate EWWW WebP';
	}

	public static function showInMenu() {
		return true;
	}

	/**
	 * Controls if this task stops on an error.
	 *
	 * @return bool
	 */
	public static function stopOnError() {
		return false;
	}

	/**
	 * Bulk action title.
	 *
	 * @return string|null
	 */
	public static function bulkActionTitle() {
		return 'Generate EWWW WebP';
	}

	/**
	 * Determines if a task is a user facing task.
	 * @return bool|false
	 */
	public static function userTask() {
		return true;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/generate-ewww-webp';
	}

	public static function warnOption() {
		return 'generate-ewww-webp-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'I UNDERSTAND';
	}

	public static function warnConfirmationText() {
		return "It is important that you backup your database prior to running this import task.  To continue, please type 'I UNDERSTAND' to confirm that you have backed up your database.";
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.generate-ewww-webp';
	}

	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [
			'selected-items' => [
				"title" => "Selected Media",
				"description" => "If you want to process just a small subset of items, click on 'Select Media'",
				"type" => "media-select",
				"media-types" => ['image']
			],
			'skip-processed' => [
				"title" => "Skip Processed",
				"description" => "Skip items that have already been processed.",
				"type" => "checkbox",
				"default" => true
			],
			'sort-order' => [
				"title" => "Sort Order",
				"description" => "Controls the order that items from your media library are migrated to cloud storage.",
				"type" => "select",
				"options" => [
					'default' => 'Default',
					'date-asc' => "Oldest first",
					'date-desc' => "Newest first",
					'title-asc' => "Title, A-Z",
					'title-desc' => "Title, Z-A",
					'filename-asc' => "File name, A-Z",
					'filename-desc' => "File name, Z-A",
				],
				"default" => 'default',
			],
		];
	}

	//endregion

	//region Data

	protected function filterPostArgs($args) {
		$args['post_mime_type'] = 'image';

		if (isset($this->options['skip-processed'])) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => 'formats";a:1:{s:4:"webp"',
						'compare' => 'NOT LIKE',
						'type'    => 'CHAR',
					],
				],
				[
					'relation' => 'OR',
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"s3"',
						'compare' => 'LIKE',
						'type'    => 'CHAR',
					],
					[
						'key'     => 'ilab_s3_info',
						'compare' => 'EXISTS',
					],
				]
			];
		} else {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => '_wp_attachment_metadata',
					'value'   => '"s3"',
					'compare' => 'LIKE',
					'type'    => 'CHAR',
				],
				[
					'key'     => 'ilab_s3_info',
					'compare' => 'EXISTS',
				],
			];
		}

		return $args;
	}

	//region Reporter
	public function reporter() {
		if (empty($this->reportHeaders)) {
			$allSizes = ilab_get_image_sizes();
			$sizeKeys = array_keys($allSizes);
			sort($sizeKeys);

			$this->reportHeaders = array_merge(array_merge([
				'Post ID',
				'Full URL',
			], $sizeKeys), ['Notes']);
		}

		return parent::reporter();
	}
	//endregion

	//endregion

	//region Execution

	public function willStart() {
		add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true');
		parent::willStart();
	}

	public function didFinish() {
		remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true');
		parent::didFinish();
	}

	private function generateWebp($postId, &$data, $size) {
		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];

		$key = arrayPath($data, 's3/key', null);
		$url = wp_get_attachment_image_url($postId, $size);
		if (empty($url)) {
			Logger::warning("Invalid url for size $size for $postId ... skipping", [], __METHOD__, __LINE__);
			return;
		}

		$response = wp_remote_head( $url, array( 'timeout' => 5 ) );
		if (is_wp_error($response) || !in_array(wp_remote_retrieve_response_code($response), [200, 301, 302])) {
			Logger::warning("Invalid HTTP response for size $size for $postId ... skipping", [], __METHOD__, __LINE__);
			return;
		}

		$tempFile = WP_CONTENT_DIR.'/uploads/mcloud-temp/'.basename($url);
		if (!file_exists(pathinfo($tempFile, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($tempFile, PATHINFO_DIRNAME), 0777, true);
		}

		file_put_contents($tempFile, file_get_contents($url));
		$webpFile = $tempFile.'.webp';

		ewww_image_optimizer_webp_create($tempFile, null, get_post_mime_type($postId), null, true);
		@unlink($tempFile);
		if (!file_exists($webpFile)) {
			Logger::warning("Failed to create webp for size $size for $postId ... skipping", [], __METHOD__, __LINE__);
		} else {
			Logger::info("Created webp for size $size for $postId", [], __METHOD__, __LINE__);
			$webpKey = $key.'.webp';

			$privacy = 'public-read';
			if ($size !== 'full') {
				$privacy = StorageToolSettings::privacy('image');
				$privacy = ImageSizePrivacy::privacyForSize($size, $privacy);
			}
			$privacy = arrayPath($data, 's3/privacy', $privacy);

			try {
				$storageTool->client()->upload($webpKey, $webpFile, $privacy);
				@unlink($webpFile);
				$data['s3']['formats']['webp'] = $webpKey;
			} catch(\Exception $ex) {
				Logger::error("Error uploading webp file for $postId: ".$ex->getMessage(), [], __METHOD__, __LINE__);
			}
		}
	}


	/**
	 * Performs the actual task
	 *
	 * @param $item
	 *
	 * @return bool|void
	 * @throws \Exception
	 */
	public function performTask($item) {
		$post_id = $item['id'];

		if (!postIdExists($post_id)) {
			return true;
		}

		$this->updateCurrentPost($post_id);

		$meta = get_post_meta($post_id, '_wp_attachment_metadata', true);
		if (empty($meta) || empty(arrayPath($meta, 'sizes', null))) {
			Logger::info("Missing metadata or image sizes for $post_id ... skipping", [], __METHOD__, __LINE__);
			return true;
		}

		if (empty(arrayPath($meta, 's3', 'key', null))) {
			Logger::warning("No S3 key for $post_id ... skipping", [], __METHOD__, __LINE__);
			return true;
		}

		foreach($meta['sizes'] as $size => &$sizeData) {
			if (empty(arrayPath($sizeData, 's3', null))) {
				Logger::warning("No S3 data for $post_id for size $size ... skipping", [], __METHOD__, __LINE__);
				continue;
			}

			$this->generateWebp($post_id, $sizeData, $size);
		}

		$this->generateWebp($post_id, $meta, 'full');
		update_post_meta($post_id, '_wp_attachment_metadata', $meta);

		return true;
	}

	//endregion
}