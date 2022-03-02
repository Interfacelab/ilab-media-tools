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

use function MediaCloud\Plugin\Utilities\anyNull;
use MediaCloud\Plugin\Wizard\WizardBuilder;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class DigitalOceanStorage extends OtherS3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'do';
	}

	public static function name() {
		return 'DigitalOcean Spaces';
	}

	public static function bucketLink($bucket) {
		return "https://cloud.digitalocean.com/spaces/$bucket";
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		return "https://cloud.digitalocean.com/spaces/{$bucket}?path={$key}";
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	public static function settingsErrorOptionName() {
		return 'ilab-do-settings-error';
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-direct-upload-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-s3.js', [], MEDIA_CLOUD_VERSION, true);
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
			$builder = new WizardBuilder('cloud-storage-do', true);
		}

		$builder
			->section('cloud-storage-do', true)
				->select('Getting Started', 'Learn about DigitalOcean Spaces and how to setup spaces to work with Media Cloud.')
					->group('wizard.cloud-storage.providers.do.intro', 'select-buttons')
						->option('read-tutorial', 'Step By Step Tutorial', null, null, 'cloud-storage-do-tutorial')
					->endGroup()
				->endStep()
				->form('wizard.cloud-storage.providers.do.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'do')
					->textField('mcloud-storage-s3-access-key', 'Access Key', '', null)
					->passwordField('mcloud-storage-s3-secret', 'Secret', '', null)
					->textField('mcloud-storage-s3-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
					->hiddenField('mcloud-storage-s3-region', 'auto')
					->selectField('mcloud-storage-s3-endpoint', 'Custom Endpoint', "", null, [
						'nyc3.digitaloceanspaces.com' => 'nyc3.digitaloceanspaces.com',
						'sfo2.digitaloceanspaces.com' => 'sfo2.digitaloceanspaces.com',
						'sfo3.digitaloceanspaces.com' => 'sfo3.digitaloceanspaces.com',
						'sgp1.digitaloceanspaces.com' => 'sgp1.digitaloceanspaces.com',
						'fra1.digitaloceanspaces.com' => 'fra1.digitaloceanspaces.com',
						'ams3.digitaloceanspaces.com' => 'ams3.digitaloceanspaces.com',
					])
					->hiddenField('mcloud-storage-s3-use-path-style-endpoint', true)
				->endStep()
				->testStep('wizard.cloud-storage.providers.do.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.do.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		$builder
			->tutorialSection('cloud-storage-do-tutorial', true)
				->tutorial('wizard.cloud-storage.providers.do.tutorial.step-1', 'Create Space', 'Create the space you will be using with Media Cloud.')
				->tutorial('wizard.cloud-storage.providers.do.tutorial.step-2', 'Create API Key', 'Generate the API keys Media Cloud uses to access spaces.', null, true)
				->tutorial('wizard.cloud-storage.providers.do.tutorial.step-3', 'CORS Configuration (Optional)', 'If you plan on using the Direct Uploads functionality, learn how to configure CORS to make that happen.', null, true)
		->endSection();

		return $builder;
	}

	protected static function validateWizardInput($provider, $accessKey, $secret, $bucket, $region, $endpoint) {
		return !anyNull($provider, $accessKey, $secret, $bucket, $endpoint);
	}
	//endregion

}
