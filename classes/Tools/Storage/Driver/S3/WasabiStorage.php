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
use function MediaCloud\Plugin\Utilities\anyNull;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Wizard\WizardBuilder;
use MediaCloud\Vendor\Aws\Exception\AwsException;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class WasabiStorage extends OtherS3Storage {
	//region Static Information Methods
	public static function identifier() {
		return 'wasabi';
	}

	public static function name() {
		return 'Wasabi';
	}

	public static function bucketLink($bucket) {
		return "https://console.wasabisys.com/#/file_manager/$bucket";
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		return "https://console.wasabisys.com/#/file_manager/$bucket/$key";
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	public static function settingsErrorOptionName() {
		return 'ilab-wasabi-settings-error';
	}

	public static function endpoint() {
		$region = Environment::Option('mcloud-storage-wasabi-region', null, null);
		if(!empty($region)) {
			return "https://s3.{$region}.wasabisys.com";
		}

		return "https://s3.wasabisys.com";
	}

	public static function pathStyleEndpoint() {
		return true;
	}

	public static function defaultRegion() {
		return null;
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions

	public function insureACL($key, $acl) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$this->client->putObjectAcl(['Bucket' => $this->settings->bucket, 'Key' => $key, 'ACL' => $acl]);
		} catch (AwsException $ex) {
			throw new StorageException($ex->getMessage(), $ex->getStatusCode(), $ex);
		}
	}

	public function info($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$result = $this->client->headObject(['Bucket' => $this->settings->bucket, 'Key' => $key]);
			$length = $result->get('ContentLength');
			$type = $result->get('ContentType');
		}
		catch(AwsException $ex) {
			throw new StorageException($ex->getMessage(), $ex->getStatusCode(), $ex);
		}

		$url = $this->url($key);
		$presignedUrl = $this->presignedUrl($key);

		$size = null;
		if(strpos($type, 'image/') === 0) {
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
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		try {
			$optionsData = [
				'Bucket' => $this->settings->bucket,
				'ContentType' => $mimeType,
				'Key' => $key
			];

			if(!empty($cacheControl)) {
				$optionsData[] = ['CacheControl' => $cacheControl];
			}

			if(!empty($expires)) {
				$optionsData[] = ['Expires' => $expires];
			}



			$putCommand = $this->client->getCommand('PutObject',$optionsData);
			$request = $this->client->createPresignedRequest($putCommand, '+20 minutes');
			$signedUrl = (string)$request->getUri();

			return new OtherS3UploadInfo($key,$signedUrl,$acl);
		}
		catch(\Exception $ex) {
			Logger::error('S3 Generate File Upload URL Error', ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-upload-other-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-other-s3.js', [], MEDIA_CLOUD_VERSION, true);
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
			$builder = new WizardBuilder('cloud-storage-wasabi', true);
		}

		$builder
			->section('cloud-storage-wasabi', true)
				->select('Getting Started', 'Learn about Wasabi and how to set it up to work with Media Cloud.')
					->group('wizard.cloud-storage.providers.wasabi.intro', 'select-buttons')
						->option('read-tutorial', 'Step By Step Tutorial', null, null, 'cloud-storage-wasabi-tutorial')
					->endGroup()
				->endStep()
				->form('wizard.cloud-storage.providers.wasabi.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'wasabi')
					->textField('mcloud-storage-s3-access-key', 'Access Key', '', null)
					->passwordField('mcloud-storage-s3-secret', 'Secret', '', null)
					->textField('mcloud-storage-s3-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
					->selectField('mcloud-storage-wasabi-region', 'Region', '', null, [
						'us-east-1' => 'US East 1',
						'us-east-2' => 'US East 2',
						'us-west-1' => 'US West',
						'us-central' => 'US Central',
						'eu-central-1' => 'EU (Amsterdam)',
						'eu-west-1' => 'EU (London)',
						'eu-west-2' => 'EU (Paris)',
						'ap-northeast-1' => 'Asia Pacific (Tokyo)',
						'ap-northeast-2' => 'Asia Pacific (Osaka)',
					])
				->endStep()
				->testStep('wizard.cloud-storage.providers.wasabi.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.wasabi.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		$builder
			->tutorialSection('cloud-storage-wasabi-tutorial', true)
				->tutorial('wizard.cloud-storage.providers.wasabi.tutorial.step-1', 'Create IAM User', 'Create the IAM user account and credentials Media Cloud will use to access Wasabi.')
				->tutorial('wizard.cloud-storage.providers.wasabi.tutorial.step-2', 'Create Bucket', 'Create the bucket that Media Cloud will use.', null, true)
			->endSection();

		return $builder;
	}

	protected static function validateWizardInput($provider, $accessKey, $secret, $bucket, $region, $endpoint) {
		return !anyNull($provider, $accessKey, $secret, $bucket, $region);
	}

	/**
	 * @return array[
	 *  'providerName' => string,
	 *  'accessKeyName' => string,
	 *  'secretName' => string,
	 *  'bucketName' => string,
	 *  'regionName' => string,
	 *  'endpointName' => string,
	 *  'pathStyleEndpointName' => string,
	 *  'useTransferAccelerationName' => string
	 * ]
	 */
	protected static function fetchWizardInputNames() {
		return [
			'providerName' => 'mcloud-storage-provider',
			'accessKeyName' => 'mcloud-storage-s3-access-key',
			'secretName' => 'mcloud-storage-s3-secret',
			'bucketName' => 'mcloud-storage-s3-bucket',
			'regionName' => 'mcloud-storage-wasabi-region',
			'endpointName' => 'mcloud-storage-s3-endpoint',
			'pathStyleEndpointName' => 'mcloud-storage-s3-use-path-style-endpoint',
			'useTransferAccelerationName' => 'mcloud-storage-s3-use-transfer-acceleration'
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
