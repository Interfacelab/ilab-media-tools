<?php

namespace MediaCloud\Plugin\Tools\Storage\Driver\BunnyCDN;

use MediaCloud\Plugin\Tools\Storage\InvalidStorageSettingsException;
use MediaCloud\Plugin\Tools\Storage\StorageInterface;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Wildcard;
use MediaCloud\Plugin\Wizard\ConfiguresWizard;
use MediaCloud\Plugin\Wizard\StorageWizardTrait;
use MediaCloud\Plugin\Wizard\WizardBuilder;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\anyNull;
use function MediaCloud\Plugin\Utilities\arrayPath;

class BunnyCDNStorage implements StorageInterface, ConfiguresWizard {
	use StorageWizardTrait;

	private $client = null;

	//region Properties
	/** @var BunnyCDNSettings|null  */
	protected $settings = null;
	//endregion

	//region Constructor
	public function __construct() {
		$this->settings = new BunnyCDNSettings();

		if($this->enabled()) {
			add_filter('media-cloud/storage/sign-url', '__return_true');
			$this->client = new BunnyCDNClient($this->settings->apiKey, $this->settings->storageZone, $this->settings->region, $this->settings->pullZone);
		}
	}
	//endregion


	/**
	 * @inheritDoc
	 */
	public function supportsDirectUploads() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsWildcardDirectUploads() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function identifier() {
		return 'bunnycdn';
	}

	/**
	 * @inheritDoc
	 */
	public static function name() {
		return 'Bunny CDN';
	}

	/**
	 * @inheritDoc
	 */
	public static function endpoint() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function defaultRegion() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function pathStyleEndpoint() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function bucketLink($bucket) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function pathLink($bucket, $key) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function usesSignedURLs($type = null) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function signedURLExpirationForType($type = null) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function enabled() {
		if(anyEmpty($this->settings->apiKey && $this->settings->pullZone && $this->settings->storageZone)) {
			if (current_user_can('manage_options')) {
				$adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
				NoticeManager::instance()->displayAdminNotice('error', "To start using Cloud Storage, you will need to <a href='$adminUrl'>supply your Bunny CDN credentials.</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');
			}

			return false;
		}

		if (!function_exists('ftp_connect')) {
			if (current_user_can('manage_options')) {
				NoticeManager::instance()->displayAdminNotice('error', "To use Bunny CDN you must install the <a href='https://www.php.net/manual/en/book.ftp.php' target='_blank'>FTP PHP extension</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');
			}

			return false;
		}

		if($this->settings->settingsError) {
			if (current_user_can('manage_options')) {
				NoticeManager::instance()->displayAdminNotice('error', 'Your Bunny CDN settings are incorrect or the bucket does not exist.  Please verify your settings and update them.');
			}

			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsError() {
		return $this->settings->settingsError;
	}

	/**
	 * @inheritDoc
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * @inheritDoc
	 */
	public function validateSettings($errorCollector = null) {
		delete_option('ilab-bunnycdn-settings-error');
		$this->settings->settingsError = false;

		if ($this->enabled()) {
			$client = $this->client;
			$this->client = null;

			if ($client) {
				$files = $client->listFiles('/');

				Logger::info('Files:'.json_encode($files, JSON_PRETTY_PRINT));
				if ($files === false) {
					$this->settings->settingsError = true;
					update_option('ilab-bunnycdn-settings-error', true);
					if ($errorCollector) {
						$errorCollector->addError("Unable to connect to Bunny CDN API.");
					}
				} else {
					$this->client = $client;
					return true;
				}
			}
		} else {
			Logger::info('Not enabled');
			if ($errorCollector) {
				$errorCollector->addError("Bunny CDN Settings are invalid.");
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function client() {
		return $this->client;
	}

	/**
	 * @inheritDoc
	 */
	public function bucket() {
		return $this->settings->storageZone;
	}

	/**
	 * @inheritDoc
	 */
	public function region() {
		return $this->settings->region;
	}

	/**
	 * @inheritDoc
	 */
	public function isUsingPathStyleEndPoint() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function exists($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException("Bunny CDN settings are invalid.");
		}

		return $this->client->exists($key);
	}

	/**
	 * @inheritDoc
	 */
	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
	}

	/**
	 * @inheritDoc
	 */
	public function upload($key, $fileName, $acl, $cacheControl = null, $expires = null, $contentType = null, $contentEncoding = null, $contentLength = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException("Bunny CDN settings are invalid.");
		}

		Logger::startTiming("Start Upload", ['file' => $key], __METHOD__, __LINE__);
		$res = $this->client->upload($fileName, $key);
		Logger::endTiming("End Upload", ['file' => $key], __METHOD__, __LINE__);

		if ($res) {
			return $this->settings->pullZone.'/'.$key;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function createDirectory($key) {
		$this->client->mkdir($key);
	}

	/**
	 * @inheritDoc
	 */
	public function deleteDirectory($key) {
	}

	/**
	 * @inheritDoc
	 */
	public function delete($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException("Bunny CDN settings are invalid.");
		}

		$this->client->deleteFile($key);
	}

	/**
	 * @inheritDoc
	 */
	public function info($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->info($key);
	}

	/**
	 * @inheritDoc
	 */
	public function acl($key) {
	}

	/**
	 * @inheritDoc
	 */
	public function insureACL($key, $acl) {
	}

	/**
	 * @inheritDoc
	 */
	public function updateACL($key, $acl) {
	}

	/**
	 * @inheritDoc
	 */
	public function canUpdateACL() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function presignedUrl($key, $expiration = 0, $options = []) {
		return $this->client->signUrl($this->settings->tokenAuthKey, $this->settings->pullZone, $key, $expiration, false);//str_ends_with($key, '.m3u8'));
	}

	/**
	 * @inheritDoc
	 */
	public function url($key, $type = null) {
		$matches = explode("\n", $this->settings->signedMatches);
		foreach($matches as $match) {
			if (empty($match)) {
				continue;
			}

			$wildcard = new Wildcard($match);
			if ($wildcard->match($key)) {
				return $this->presignedUrl($key, 3600, []);
			}
		}

		return $this->settings->pullZone . '/' . $key;
	}

	/**
	 * @inheritDoc
	 */
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueUploaderScripts() {
	}

	/**
	 * @inheritDoc
	 */
	public function dir($path = '', $delimiter = '/', $limit = -1, $next = null) {
		$files =  $this->client->listFiles($path);
		return ['next' => null, 'files' => $files];
	}

	/**
	 * @inheritDoc
	 */
	public function ls($path = '', $delimiter = '/', $limit = -1, $next = null, $recursive = false) {
		$files =  $this->client->listFiles($path);

		$fileNames = [];
		foreach($files as $file) {
			if ($file->getType() !== 'folder') {
				$fileNames[] = $file->getName();
			}
		}

		return ['next' => null, 'files' => $fileNames];
	}

	/**
	 * @inheritDoc
	 */
	public function supportsBrowser() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function prepareOptimizationInfo() {
		return [];
	}

	public static function configureWizard($builder = null) {
		if (empty($builder)) {
			$builder = new WizardBuilder('cloud-storage-bunnycdn', true);
		}

		$builder
			->section('cloud-storage-bunnycdn', true)
				->select('Getting Started', 'Learn about Bunny CDN and how to set it up to work with Media Cloud.')
					->group('wizard.cloud-storage.providers.bunnycdn.intro', 'select-buttons')
						->option('read-tutorial', 'Step By Step Tutorial', null, null, 'cloud-storage-bunnycdn-tutorial')
					->endGroup()
				->endStep()
				->form('wizard.cloud-storage.providers.bunnycdn.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'bunnycdn')
					->passwordField('mcloud-storage-bunnycdn-apikey', 'API Key', '', null)
					->textField('mcloud-storage-bunnycdn-storage-zone', 'Storage Zone', '', null)
					->textField('mcloud-storage-bunnycdn-pull-zone', 'Pull Zone URL', '', null)
					->selectField('mcloud-storage-bunnycdn-region', 'Region', '', null, [
						'' => 'Falkenstein: storage.bunnycdn.com',
						'ny' => 'New York: ny.storage.bunnycdn.com',
						'la' => 'Los Angeles: la.storage.bunnycdn.com',
						'sg' => 'Singapore: sg.storage.bunnycdn.com',
						'syd' => 'Sydney: syd.storage.bunnycdn.com',
					])
				->endStep()
				->testStep('wizard.cloud-storage.providers.bunnycdn.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.bunnycdn.success', 'select-buttons')
			->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
			->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
			->endStep();

		$builder
			->tutorialSection('cloud-storage-bunnycdn-tutorial', true)
			->tutorial('wizard.cloud-storage.providers.bunnycdn.tutorial.step-1', 'Create Storage Zone', 'Create the storage zone you will be using with Media Cloud.')
			->tutorial('wizard.cloud-storage.providers.bunnycdn.tutorial.step-2', 'Add a Pull Zone', 'Create a pull zone for your storage zone.', null, true)
			->endSection();

		return $builder;
	}

	public static function processWizardSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-storage-settings')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		$providerName = 'mcloud-storage-provider';
		$apiKeyName = 'mcloud-storage-bunnycdn-apikey';
		$storageZoneName = 'mcloud-storage-bunnycdn-storage-zone';
		$pullZoneName = 'mcloud-storage-bunnycdn-pull-zone';
		$regionName = 'mcloud-storage-bunnycdn-region';

		$provider = arrayPath($_POST, $providerName, null);
		$apiKey = arrayPath($_POST, $apiKeyName, null);
		$storageZone = arrayPath($_POST, $storageZoneName, null);
		$pullZone = arrayPath($_POST, $pullZoneName, null);
		$region = arrayPath($_POST, $regionName, '');

		if (anyNull($provider, $apiKey, $storageZone, $pullZone)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing required fields'], 200);
		}

		$oldProvider = Environment::ReplaceOption($providerName, $provider);
		$oldApiKey = Environment::ReplaceOption($apiKeyName, $apiKey);
		$oldStorageZone = Environment::ReplaceOption($storageZoneName, $storageZone);
		$oldPullZone = Environment::ReplaceOption($pullZoneName, $pullZone);
		$oldRegion = Environment::ReplaceOption($regionName, $region);

		StorageToolSettings::resetStorageInstance();

		try {
			$storage = new static();
			$restoreOld = !$storage->validateSettings();
		} catch (\Exception $ex) {
			$restoreOld = true;
		}

		if ($restoreOld) {
			Environment::UpdateOption($providerName, $oldProvider);
			Environment::UpdateOption($apiKeyName, $oldApiKey);
			Environment::UpdateOption($storageZoneName, $oldStorageZone);
			Environment::UpdateOption($pullZoneName, $oldPullZone);
			Environment::UpdateOption($regionName, $oldRegion);

			StorageToolSettings::resetStorageInstance();

			$message = "There was a problem with your settings.  Please double check entries for potential mistakes.";

			wp_send_json([ 'status' => 'error', 'message' => $message], 200);
		} else {
			wp_send_json([ 'status' => 'ok'], 200);
		}
	}
}