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

use MediaCloud\Plugin\Wizard\WizardBuilder;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class MinioStorage extends OtherS3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'minio';
	}

	public static function name() {
		return 'Minio';
	}

	public static function bucketLink($bucket) {
		$instance = new self();
		return $instance->settings->endpoint.'/minio/'.$bucket;
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		$instance = new self();
		return $instance->settings->endpoint.'/minio/'.$bucket.'/'.$key;
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	public static function settingsErrorOptionName() {
		return 'ilab-minio-settings-error';
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		$args = [
			'Bucket' => $this->settings->bucket,
			'Key' => $key
		];

		if (!empty($mimeType)) {
			$args['ContentType'] = $mimeType;
		}

		if (!empty($cacheControl)) {
			$args['CacheControl'] = $cacheControl;
		}

		if (!empty($expires)) {
			$args['Expires'] = $expires;
		}


		$command = $this->client->getCommand('PutObject', $args);
		$presignedReq = $this->client->createPresignedRequest($command, '+15 minutes');

		return new OtherS3UploadInfo($key, $presignedReq->getUri()->__toString(), $acl);
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
			$builder = new WizardBuilder('cloud-storage-minio', true);
		}

		$builder
			->section('cloud-storage-minio', true)
				->intro('wizard.cloud-storage.providers.minio.intro', 'About Minio', '')
			->form('wizard.cloud-storage.providers.minio.form', 'Minio Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
				->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
				->hiddenField('mcloud-storage-provider', 'minio')
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
			->testStep('wizard.cloud-storage.providers.minio.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.minio.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		return $builder;
	}
	//endregion

}
