<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Tools\Storage\Driver\Cloudflare;

use MediaCloud\Plugin\Tools\Storage\Driver\S3\OtherS3Storage;
use MediaCloud\Plugin\Tools\Storage\Driver\S3\S3UploadInfo;
use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Vendor\Aws\CloudFront\CloudFrontClient;
use MediaCloud\Plugin\Tools\Storage\InvalidStorageSettingsException;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\S3\PostObjectV4;
use function MediaCloud\Plugin\Utilities\anyNull;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Wizard\WizardBuilder;
use function MediaCloud\Plugin\Utilities\arrayPath;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class CloudflareStorage extends OtherS3Storage {
	//region Static Information Methods
	public static function identifier() {
		return 'cloudflare';
	}

	public static function name() {
		return 'cloudflare';
	}

	public static function bucketLink($bucket) {
		return null;
	}

	public function pathLink($bucket, $key) {
		return null;
	}
	//endregion

	//region Constructor
	public function __construct() {
		parent::__construct();

		$settings = $this->settings;

		add_filter('media-cloud/storage/override-cdn', function($cdn) use ($settings) {
			if (empty($cdn)) {
				return $settings->publicBucketUrl;
			}

			return $cdn;
		});

		add_filter('media-cloud/storage/override-doc-cdn', function($cdn) use ($settings) {
			if (empty($cdn)) {
				return $settings->publicBucketUrl;
			}

			return $cdn;
		});
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	public static function settingsErrorOptionName() {
		return 'mcloud-storage-cloudflare-settings-error';
	}

	public static function defaultRegion() {
		return 'auto';
	}

	public static function pathStyleEndpoint() {
		return true;
	}

	public static function settingsClass() {
		return CloudflareStorageSettings::class;
	}

	public function enabled() {
		if (!($this->settings->key && $this->settings->secret && $this->settings->bucket && $this->settings->endpoint && $this->settings->publicBucketUrl)) {
			if (current_user_can('manage_options')) {
				$adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
				NoticeManager::instance()->displayAdminNotice('info', "Welcome to Media Cloud!  To get started, <a href='$adminUrl'>configure your cloud storage</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');
			}

			return false;
		}

		if ($this->settings->settingsError) {
			return false;
		}

		return true;
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions

	public function insureACL($key, $acl) {
	}

	public function acl($key) {
		return 'public-read';
	}

	public function updateACL($key, $acl) {

	}

	public function canUpdateACL() {
		return false;
	}

	//endregion

	//region URLs

	public function presignedUrl($key, $expiration = 0, $options = []) {
		return $this->url($key);
	}

	public function url($key, $type = null) {
		return trailingslashit($this->settings->publicBucketUrl).$key;
	}
	//endregion

	//region Direct Uploads
	/**
	 * Returns the options data for generating the policy for uploads
	 *
	 * @param $acl
	 * @param $key
	 *
	 * @return array
	 */
	protected function getOptionsData($acl, $key) {
		$keyparts = explode('.', $key);

		if ($this->canUpdateACL()) {
			return [
				['bucket' => $this->settings->bucket],
//				['key' => $key],
				['starts-with', '$key', $keyparts[0]],
				['starts-with', '$Content-Type', '']
			];
		} else {
			return [
				['bucket' => $this->settings->bucket],
//				['key' => $key],
				['starts-with', '$key', $keyparts[0]],
				['starts-with', '$Content-Type', '']
			];
		}
	}

	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		try {
			$options = [
				'Bucket' => $this->settings->bucket,
				'Key' => $key,
			];

			if ($mimeType) {
				$options['ContentType'] = $mimeType;
			}

			if ($cacheControl) {
				$options['CacheControl'] = $cacheControl;
			}

			if ($expires) {
				$options['Expires'] = $expires;
			}

			$command = $this->client->getCommand('PutObject', $options);
			$result = $this->client->createPresignedRequest($command, '+15 minutes');
			$url =  (string)$result->getUri();

			return new CloudflareUploadInfo($url, $key, $mimeType, $acl, $cacheControl, $expires);
		} catch(AwsException $ex) {
			Logger::error('S3 Generate File Upload URL Error', ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function enqueueUploaderScripts() {
//		wp_enqueue_script('ilab-media-direct-upload-other-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-other-s3.js', [], MEDIA_CLOUD_VERSION, true);
		wp_enqueue_script('ilab-media-direct-upload-r2', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-r2.js', [], MEDIA_CLOUD_VERSION, true);
//		wp_enqueue_script('ilab-media-upload-other-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-other-s3.js', [], MEDIA_CLOUD_VERSION, true);
	}
	//endregion

	//region Wizard
	/**
	 * @param WizardBuilder|null $builder
	 *
	 * @return WizardBuilder|null
	 * @throws \Exception
	 */
	public static function configureWizard($builder = null) {
		if (empty($builder)) {
			$builder = new WizardBuilder('cloud-storage-cloudflare', true);
		}

		$builder
			->section('cloud-storage-cloudflare', true)
				->select('Getting Started', 'Learn about Cloudflare R2 and how to set it up to work with Media Cloud.')
					->group('wizard.cloud-storage.providers.cloudflare.intro', 'select-buttons')
						->option('watch-tutorial', 'Watch Video Tutorial', null, null, 'cloud-storage-cloudflare-video-tutorial')
					->endGroup()
				->endStep()
				->form('wizard.cloud-storage.providers.cloudflare.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'cloudflare')
					->textField('mcloud-storage-s3-access-key', 'Access Key', '', null)
					->passwordField('mcloud-storage-s3-secret', 'Secret Key', '', null)
					->textField('mcloud-storage-s3-bucket', 'Bucket Name', 'The name of your bucket.', null)
					->textField('mcloud-storage-s3-endpoint', 'Endpoint URL', 'The private URL for your bucket.', null)
					->textField('mcloud-storage-cloudflare-r2-public-url', 'Public Bucket URL', 'The public URL for your bucket.', null)
				->endStep()
				->testStep('wizard.cloud-storage.providers.cloudflare.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.cloudflare.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		$builder->section('cloud-storage-cloudflare-video-tutorial', false)
			->video('https://www.youtube.com/watch?v=GDL2bzYMgLY', null, null, null, true)
		->endSection();
//		$builder
//			->tutorialSection('cloud-storage-wasabi-tutorial', true)
//				->tutorial('wizard.cloud-storage.providers.wasabi.tutorial.step-1', 'Create IAM User', 'Create the IAM user account and credentials Media Cloud will use to access Wasabi.')
//				->tutorial('wizard.cloud-storage.providers.wasabi.tutorial.step-2', 'Create Bucket', 'Create the bucket that Media Cloud will use.', null, true)
//			->endSection();

		return $builder;
	}

	public static function processWizardSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-storage-settings')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		/**
		 * @var string $providerName
		 * @var string $accessKeyName
		 * @var string $secretName
		 * @var string $bucketName
		 * @var string $endpointName
		 * @var string $publicBucketUrlName
		 */
		extract(static::fetchWizardInputNames(), EXTR_OVERWRITE);

		$provider = arrayPath($_POST, $providerName, null);
		$accessKey = arrayPath($_POST, $accessKeyName, null);
		$secret = arrayPath($_POST, $secretName, null);
		$bucket = arrayPath($_POST, $bucketName, null);
		$endpoint = arrayPath($_POST, $endpointName, null);
		$publicBucketUrl = arrayPath($_POST, $publicBucketUrlName, null);

		if (anyNull($provider, $accessKey, $secret, $bucket, $publicBucketUrl)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing required fields'], 200);
		}

		$oldProvider = Environment::ReplaceOption($providerName, $provider);
		$oldBucket = Environment::ReplaceOption($bucketName, $bucket);
		$oldKey = Environment::ReplaceOption($accessKeyName, $accessKey);
		$oldSecret = Environment::ReplaceOption($secretName, $secret);
		$oldEndpoint = Environment::ReplaceOption($endpointName, $endpoint);
		$oldPublicBucketUrl = Environment::ReplaceOption($publicBucketUrlName, $publicBucketUrl);

		StorageToolSettings::resetStorageInstance();

		try {
			$storage = new static();
			$restoreOld = !$storage->validateSettings();
		} catch (\Exception $ex) {
			$restoreOld = true;
		}

		if ($restoreOld) {
			Environment::UpdateOption($providerName, $oldProvider);
			Environment::UpdateOption($bucketName, $oldBucket);
			Environment::UpdateOption($accessKeyName, $oldKey);
			Environment::UpdateOption($secretName, $oldSecret);
			Environment::UpdateOption($endpointName, $oldEndpoint);
			Environment::UpdateOption($publicBucketUrlName, $oldPublicBucketUrl);

			StorageToolSettings::resetStorageInstance();

			$message = "There was a problem with your settings.  Please double check entries for potential mistakes.";

			wp_send_json([ 'status' => 'error', 'message' => $message], 200);
		} else {
			wp_send_json([ 'status' => 'ok'], 200);
		}
	}

	protected static function validateWizardInput($provider, $accessKey, $secret, $bucket, $endpoint, $publicBucketUrl) {
		return !anyNull($provider, $accessKey, $secret, $bucket, $publicBucketUrl);
	}

	/**
	 * @return array[
	 *  'providerName' => string,
	 *  'accessKeyName' => string,
	 *  'secretName' => string,
	 *  'bucketName' => string,
	 *  'endpointName' => string,
	 *  'publicBucketUrlName' => string,
	 * ]
	 */
	protected static function fetchWizardInputNames() {
		return [
			'providerName' => 'mcloud-storage-provider',
			'accessKeyName' => 'mcloud-storage-s3-access-key',
			'secretName' => 'mcloud-storage-s3-secret',
			'bucketName' => 'mcloud-storage-s3-bucket',
			'endpointName' => 'mcloud-storage-s3-endpoint',
			'publicBucketUrlName' => 'mcloud-storage-cloudflare-r2-public-url',
		];
	}

	public static function testStorageAcl() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		Tracker::trackView("System Test - Test Public", "/system-test/public");

		$client = new static();
		$errors = [];
		try {
			$result = null;
			$url = $client->url('_troubleshooter/sample.txt');

			$result = ilab_file_get_contents($url);

			if ($result != file_get_contents(ILAB_TOOLS_DIR.'/public/text/sample-upload.txt')) {
				$errors[] = "Upload <a href='$url'>sample file</a> is not publicly viewable.";
			}
		} catch (\Exception $ex) {
			$errors[] = $ex->getMessage();
		}

		if (empty($errors)) {
			wp_send_json([
				'status' => 'success',
				'message' => 'The uploaded file is publicly accessible.',
			]);
		} else {
			wp_send_json([
				'status' => 'warning',
				'message' => 'The uploaded file is not publicly accessible.  If you are using a Wasabi trial account, this is expected because trial accounts don\'t allow public files.  If you are using a paid account, double check your bucket settings because this shouldn\'t happen.',
				'errors' => $errors
			]);
		}
	}
	//endregion

}
