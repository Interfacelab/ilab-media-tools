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

use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\anyNull;
use MediaCloud\Plugin\Wizard\WizardBuilder;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class DreamHostStorage extends OtherS3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'dreamhost';
	}

	public static function name() {
		return 'DreamHost Cloud Storage';
	}

	public static function bucketLink($bucket) {
		return null;
	}

	public function pathLink($bucket, $key) {
		return null;
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return false;
	}

	public static function settingsErrorOptionName() {
		return 'ilab-dh-settings-error';
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
			$builder = new WizardBuilder('cloud-storage-dreamhost', true);
		}

		$builder
			->section('cloud-storage-dreamhost', true)
				->form('wizard.cloud-storage.providers.dreamhost.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'dreamhost')
					->textField('mcloud-storage-s3-access-key', 'Access Key', '', null)
					->passwordField('mcloud-storage-s3-secret', 'Secret', '', null)
					->textField('mcloud-storage-s3-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
					->hiddenField('mcloud-storage-s3-region', 'auto')
					->selectField('mcloud-storage-s3-endpoint', 'Custom Endpoint', "", null, [
						'objects-us-east-1.dream.io' => 'objects-us-east-1.dream.io',
					])
					->hiddenField('mcloud-storage-s3-use-path-style-endpoint', true)
				->endStep()
				->testStep('wizard.cloud-storage.providers.dreamhost.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.dreamhost.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		return $builder;
	}

	protected static function validateWizardInput($provider, $accessKey, $secret, $bucket, $region, $endpoint) {
		return !anyNull($provider, $accessKey, $secret, $bucket, $endpoint);
	}
	//endregion

}
