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

namespace MediaCloud\Plugin\Tools\Storage\Driver\S3;

use MediaCloud\Vendor\FasterImage\FasterImage;
use MediaCloud\Plugin\Tools\Storage\FileInfo;
use MediaCloud\Plugin\Tools\Storage\InvalidStorageSettingsException;
use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use function MediaCloud\Plugin\Utilities\anyNull;
use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Wizard\WizardBuilder;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class OtherS3Storage extends S3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'other-s3';
	}

	public static function name() {
		return 'Other S3 Service';
	}

	public static function bucketLink($bucket) {
		$instance = new self();
		return $instance->settings->endpoint;
	}

	public function pathLink($bucket, $key) {
		$instance = new self();
		return $instance->settings->endpoint;
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return false;
	}

	public static function settingsErrorOptionName() {
		return 'ilab-other-s3-settings-error';
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions
	public function info( $key ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$url = $this->url($key);
		$presignedUrl = $this->presignedUrl($key);

		$defaults = stream_context_get_options(stream_context_get_default());
		stream_context_set_default(['http'=>['method'=>'HEAD']]);
		$headers = get_headers($presignedUrl, 1);
		stream_context_set_default($defaults);

		if (!empty($headers[0]) && (strpos($headers[0], '403 Forbidden') !== false)) {
			stream_context_set_default(['http'=>['method'=>'HEAD']]);
			$headers = get_headers($url, 1);
			stream_context_set_default($defaults);
		}

		if (!empty($headers[0]) && (strpos($headers[0], '403 Forbidden') !== false)) {
			throw new StorageException("Cannot fetch info for $url.");
		}

		$length = arrayPath($headers, 'Content-Length', false);
		if (empty($length)) {
			$length = arrayPath($headers, 'content-length', false);
		}

		if ($length && is_array($length)) {
			$length = $length[count($length) - 1];
		}

		$type = arrayPath($headers, 'Content-Type', false);
		if (empty($type)) {
			$type = arrayPath($headers, 'content-type', false);
		}
		
		if ($type && is_array($type)) {
			$type = $type[count($type) - 1];
		}

		if (empty($type) && empty($length)) {
			throw new StorageException("Unable to get Content-Type or Content-Length for '$key'");
		}

		$size = null;
		if (strpos($type, 'image/') === 0) {
			$faster = new FasterImage();
			$result = $faster->batch([$presignedUrl]);
			$result = $result[$presignedUrl];
			$size = $result['size'];
		}

		$fileInfo = new FileInfo($key, $url, $presignedUrl, $length, $type, $size);
		return $fileInfo;
	}
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-direct-upload-other-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-other-s3.js', [], MEDIA_CLOUD_VERSION, true);
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
			$builder = new WizardBuilder('cloud-storage-other-s3', true);
		}

		$builder
			->section('cloud-storage-other-s3', true)
				->intro('wizard.cloud-storage.providers.other-s3.intro', 'About S3 Compatible Cloud Storage', '')
				->form('wizard.cloud-storage.providers.other-s3.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'other-s3')
					->textField('mcloud-storage-s3-access-key', 'Access Key', '', null)
					->passwordField('mcloud-storage-s3-secret', 'Secret', '', null)
					->textField('mcloud-storage-s3-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
					->selectField('mcloud-storage-s3-region', 'Region', "The region that your bucket is in.  Set to 'auto' to have Media Cloud automatically determine what region your bucket is in.  May not work with some S3 compatible providers.", 'auto', [
						"auto" => "Automatic",
						'us-east-2' => 'US East (Ohio)',
						'us-east-1' => 'US East (N. Virginia)',
						'us-west-1' => 'US West (N. California)',
						'us-west-2' => 'US West (Oregon)',
						'ca-central-1' => 'Canada (Central)',
						'ap-east-1' => 'Asia Pacific (Hong Kong)',
						'ap-south-1' => 'Asia Pacific (Mumbai)',
						'ap-northeast-1' => 'Asia Pacific (Tokyo)',
						'ap-northeast-2' => 'Asia Pacific (Seoul)',
						'ap-northeast-3' => 'Asia Pacific (Osaka-Local)',
						'ap-southeast-1' => 'Asia Pacific (Singapore)',
						'ap-southeast-2' => 'Asia Pacific (Sydney)',
						'eu-central-1' => 'EU (Frankfurt)',
						'eu-west-1' => 'EU (Ireland)',
						'eu-west-2' => 'EU (London)',
						'eu-west-3' => 'EU (Paris)',
						'eu-north-1' => 'EU (Stockholm)',
						'sa-east-1' => 'South America (São Paulo)',
						'cn-north-1' => 'China (Beijing)',
						'cn-northwest-1' => 'China (Ningxia)',
					])
					->textField('mcloud-storage-s3-endpoint', 'Custom Endpoint', "Some S3 compatible services use a custom API endpoint URL or server name.  For example, with a DigitalOcean space in NYC-3 region, this value would be <code>nyc3.digitaloceanspaces.com</code>", null)
					->hiddenField('mcloud-storage-s3-use-path-style-endpoint', true)
				->endStep()
			->testStep('wizard.cloud-storage.providers.other-s3.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.other-s3.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		return $builder;
	}

	protected static function validateWizardInput($provider, $accessKey, $secret, $bucket, $region, $endpoint) {
		return !anyNull($provider, $accessKey, $secret, $bucket, $region, $endpoint);
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
		 * @var string $regionName
		 * @var string $endpointName
		 * @var string $pathStyleEndpointName
		 * @var string $useTransferAccelerationName
		 */
		extract(static::fetchWizardInputNames(), EXTR_OVERWRITE);

		$provider = arrayPath($_POST, $providerName, null);
		$accessKey = arrayPath($_POST, $accessKeyName, null);
		$secret = arrayPath($_POST, $secretName, null);
		$bucket = arrayPath($_POST, $bucketName, null);
		$region = arrayPath($_POST, $regionName, 'auto');
		$endpoint = arrayPath($_POST, $endpointName, null);
		$pathStyleEndpoint = arrayPath($_POST, $pathStyleEndpointName, true);

		if (!static::validateWizardInput($provider, $accessKey, $secret, $bucket, $region, $endpoint)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing required fields'], 200);
		}

		$oldProvider = Environment::ReplaceOption($providerName, $provider);
		$oldBucket = Environment::ReplaceOption($bucketName, $bucket);
		$oldKey = Environment::ReplaceOption($accessKeyName, $accessKey);
		$oldSecret = Environment::ReplaceOption($secretName, $secret);
		$oldRegion = Environment::ReplaceOption($regionName, $region);
		$oldEndpoint = Environment::ReplaceOption($endpointName, $endpoint);
		$oldPathStyleEndpoint = Environment::ReplaceOption($pathStyleEndpointName, $pathStyleEndpoint);

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
			Environment::UpdateOption($regionName, $oldRegion);
			Environment::UpdateOption($endpointName, $oldEndpoint);
			Environment::UpdateOption($pathStyleEndpointName, $oldPathStyleEndpoint);

			StorageToolSettings::resetStorageInstance();

			$message = "There was a problem with your settings.  Please double check entries for potential mistakes.";

			wp_send_json([ 'status' => 'error', 'message' => $message], 200);
		} else {
			wp_send_json([ 'status' => 'ok'], 200);
		}
	}

	//endregion


	//region Optimization
	public function prepareOptimizationInfo() {
		return [
		];
	}
	//endregion
}
