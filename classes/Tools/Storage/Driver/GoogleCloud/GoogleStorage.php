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

namespace MediaCloud\Plugin\Tools\Storage\Driver\GoogleCloud;

use MediaCloud\Vendor\FasterImage\FasterImage;
use MediaCloud\Vendor\Google\Cloud\Core\Timestamp;
use MediaCloud\Vendor\Google\Cloud\Storage\StorageClient;
use MediaCloud\Vendor\Google\Cloud\Storage\StorageObject;
use MediaCloud\Plugin\Tools\Storage\FileInfo;
use MediaCloud\Plugin\Tools\Storage\InvalidStorageSettingsException;
use MediaCloud\Plugin\Tools\Storage\StorageConstants;
use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageFile;
use MediaCloud\Plugin\Tools\Storage\StorageInterface;
use function MediaCloud\Plugin\Utilities\anyNull;
use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\ErrorCollector;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Wizard\ConfiguresWizard;
use MediaCloud\Plugin\Wizard\StorageWizardTrait;
use MediaCloud\Plugin\Wizard\WizardBuilder;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

class GoogleStorage implements StorageInterface, ConfiguresWizard {
	use StorageWizardTrait;

	const GOOGLE_ACL = [
		StorageConstants::ACL_PRIVATE => 'authenticatedRead',
		StorageConstants::ACL_AUTHENTICATED_READ => 'authenticatedRead',
		StorageConstants::ACL_PUBLIC_READ => 'publicRead'
	];

	//region Properties
	/** @var GoogleStorageSettings */
	protected $settings = null;

	/*** @var bool */
	private $settingsError = false;

	/*** @var StorageClient */
	private $client = null;

	//endregion

	//region Constructor
	public function __construct() {
		$this->settings = new GoogleStorageSettings();

		$this->settingsError = Environment::Option('mcloud-google-settings-error', null, false);
		$this->client = $this->getClient();
	}
	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'google';
	}

	public static function name() {
		return 'Google Cloud Storage';
	}

	public static function endpoint() {
		return null;
	}

	public static function pathStyleEndpoint() {
		return null;
	}

	public static function defaultRegion() {
		return null;
	}

	public static function bucketLink($bucket) {
		return "https://console.cloud.google.com/storage/browser/$bucket";
	}

	public static function defaultDownloadUrl() {
		$url = StorageObject::DEFAULT_DOWNLOAD_URL;
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			$url = 'https://'.$url;
		}

		return $url;
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		return "https://console.cloud.google.com/storage/browser/{$bucket}/{$key}";
	}
	//endregion

	//region Enabled/Options
    public function usesSignedURLs($type = null) {
	    if (($type == null) || (!empty($this->settings->usePresignedURLs))) {
		    return $this->settings->usePresignedURLs;
	    }

	    $use = false;
	    if (strpos($type, 'image') === 0) {
		    $use = $this->settings->usePresignedURLsForImages;
	    } else if (strpos($type, 'video') === 0) {
		    $use = $this->settings->usePresignedURLsForVideo;
	    } else if (strpos($type, 'audio') === 0) {
		    $use = $this->settings->usePresignedURLsForAudio;
	    } else if (strpos($type, 'application') === 0) {
		    $use = $this->settings->usePresignedURLsForDocs;
	    }

	    if (!empty($use)) {
		    return true;
	    }

	    return $this->settings->usePresignedURLs;
    }

	public function signedURLExpirationForType($type = null) {
		if (($type == null) || (!empty($this->settings->usePresignedURLs))) {
			return $this->settings->presignedURLExpiration;
		}

		if (strpos($type, 'image') === 0) {
			return (empty($this->settings->presignedURLExpirationForImages)) ? $this->settings->presignedURLExpiration : $this->settings->presignedURLExpirationForImages;
		}

		if (strpos($type, 'video') === 0) {
			return (empty($this->settings->presignedURLExpirationForVideo)) ? $this->settings->presignedURLExpiration : $this->settings->presignedURLExpirationForVideo;
		}

		if (strpos($type, 'audio') === 0) {
			return (empty($this->settings->presignedURLExpirationForAudio)) ? $this->settings->presignedURLExpiration : $this->settings->presignedURLExpirationForAudio;
		}

		if ((strpos($type, 'application') === 0) || (strpos($type, 'text') === 0)) {
			return (empty($this->settings->presignedURLExpirationForDocs)) ? $this->settings->presignedURLExpiration : $this->settings->presignedURLExpirationForDocs;
		}

		return $this->settings->presignedURLExpiration;
	}

	public function usesBucketPolicyOnly() {
		return $this->settings->useBucketPolicyOnly;
	}

	public function supportsDirectUploads() {
		return true;
	}

	public function supportsWildcardDirectUploads() {
		return false;
	}

	public function supportsBrowser() {
		return true;
	}

    /**
     * @param ErrorCollector|null $errorCollector
     * @return bool
     * @throws StorageException
     */
	public function validateSettings($errorCollector = null) {
		Environment::DeleteOption('mcloud-google-settings-error');
		$this->settingsError = false;

		$this->client = null;
		$valid = false;
		if($this->enabled()) {
			$client = $this->getClient($errorCollector);

			if($client) {
				try {
					if($client->bucket($this->settings->bucket)->exists()) {
						$valid = true;
					} else {
                        if ($errorCollector) {
                            $errorCollector->addError("Bucket {$this->settings->bucket} does not exist.");
                        }

						Logger::error("Bucket does not exist.", [], __METHOD__, __LINE__);
					}
                } catch (\Exception $ex) {
                    if ($errorCollector) {
                        $errorCollector->addError("Error insuring that {$this->settings->bucket} exists.  Message: ".$ex->getMessage());
                    }

                    Logger::error("Google Storage Error", ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
				}
			}

			if(!$valid) {
				$this->settingsError = true;
				Environment::UpdateOption('mcloud-google-settings-error', true);
			} else {
				$this->client = $client;
			}
		} else {
            if ($errorCollector) {
                $errorCollector->addError("Google configuration is incorrect or missing.");
            }
        }

		return $valid;
	}

	public function enabled() {
		if(empty($this->settings->credentials) || (!is_array($this->settings->credentials)) || empty($this->settings->bucket)) {
			$adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
			NoticeManager::instance()->displayAdminNotice('info', "Welcome to Media Cloud!  To get started, <a href='$adminUrl'>configure your cloud storage</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');
			return false;
		}

		if($this->settingsError) {
            NoticeManager::instance()->displayAdminNotice('error', "Your Google Storage settings are incorrect, or your account doesn't have the correct permissions or the bucket does not exist.  Please verify your settings and update them.");
			return false;
		}

		return true;
	}

	public function settings() {
		return $this->settings;
	}

	public function settingsError() {
		return $this->settingsError;
	}

	public function client() {
        if ($this->client == null) {
            $this->client = $this->getClient();
        }

        return $this->client;
    }
	//endregion

	//region Client Creation
	/**
     * @param ErrorCollector|null $errorCollector
	 * @return StorageClient|null
	 */
	protected function getClient($errorCollector = null) {
		if(!$this->enabled()) {
            if ($errorCollector) {
                $errorCollector->addError("Google configuration is incorrect or missing.");
            }

            return null;
		}

		$client = null;
		if (!empty($this->settings->credentials) && is_array($this->settings->credentials)) {
			$client = new StorageClient([
				                                  'projectId' => $this->settings->credentials['project_id'],
				                                  'keyFile' => $this->settings->credentials,
                                                  'scopes' => StorageClient::FULL_CONTROL_SCOPE
			                                  ]);
		}

		if(!$client) {
            if ($errorCollector) {
                $errorCollector->addError("Google configuration is incorrect or missing.");
            }

			Logger::error('Could not create Google storage client.', [], __METHOD__, __LINE__);
		}

		return $client;
	}
	//endregion

	//region File Functions
	public function bucket() {
		return $this->settings->bucket;
	}

	public function region() {
		return null;
	}

	public function isUsingPathStyleEndPoint() {
		return false;
	}

	public function acl($key) {
		return null;
	}

	public function insureACL($key, $acl) {
		if (!$this->usesBucketPolicyOnly()) {
			$object = $this->client->bucket($this->settings->bucket)->object($key);
			$object->update(['acl' => []], ['predefinedAcl' => self::GOOGLE_ACL[$acl]]);
		}
	}

	public function updateACL($key, $acl) {
		$this->insureACL($key, $acl);
	}

	public function canUpdateACL() {
		return true;
	}

	public function exists($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->bucket($this->settings->bucket)->object($key)->exists();
	}

	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->settings->bucket);

		$sourceObject = $bucket->object($sourceKey);

		try {
			$data = [
				'name' => $destKey,
				'metadata'=> [
					'cacheControl' => $cacheControl
				]
			];

			if (!$this->usesBucketPolicyOnly()) {
				$data['predefinedAcl'] = self::GOOGLE_ACL[$acl];
			}

			$sourceObject->copy($bucket, $data);
		} catch (\Exception $ex) {
			StorageException::ThrowFromOther($ex);
		}
	}

	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->settings->bucket);

		$metadata = [];
		if (!empty($cacheControl)) {
			$metadata['cacheControl'] = $cacheControl;
		}

		if ($contentType) {
			$metadata['contentType'] = $contentType;
		}

		if ($contentEncoding) {
			$metadata['contentEncoding'] = $contentEncoding;
		}

		if ($contentLength) {
			$metadata['contentLength'] = $contentLength;
		}

		try {
			Logger::startTiming("Start Upload", ['file' => $key], __METHOD__, __LINE__);

			$data = [
				'name' => $key,
				'metadata'=> $metadata
			];

			if (!$this->usesBucketPolicyOnly()) {
				$data['predefinedAcl'] = self::GOOGLE_ACL[$acl];
			}

			$object = $bucket->upload(fopen($fileName, 'r'), $data);

			Logger::endTiming("End Upload", ['file' => $key], __METHOD__, __LINE__);
		} catch (\Exception $ex) {
			Logger::error("Error uploading $fileName ...",['exception' => $ex->getMessage()], __METHOD__, __LINE__);

			StorageException::ThrowFromOther($ex);
		}

		$url = $object->gcsUri();
		$url = str_replace('gs://', static::defaultDownloadUrl().'/', $url);

		return $url;
	}

	public function delete($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->settings->bucket);

		try {
			$bucket->object($key)->delete();
		} catch (\Exception $ex) {
			StorageException::ThrowFromOther($ex);
		}
	}

	public function info($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$object = $this->client->bucket($this->settings->bucket)->object($key);
		$info = $object->info();
		$length = $info['size'];
		$type = $info['contentType'];

		$url = $object->gcsUri();
		$url = str_replace('gs://', static::defaultDownloadUrl().'/', $url);

		$expiration = $this->settings->presignedURLExpiration;
		if (empty($expiration)) {
			$expiration = 1;
		}

		$presignedUrl = $object->signedUrl(new Timestamp(new \DateTime("+{$expiration} minutes")));

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

	public function createDirectory($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$key = trailingslashit($key);

		$bucket = $this->client->bucket($this->settings->bucket);

		try {
			Logger::startTiming("Start Create Directory", ['file' => $key], __METHOD__, __LINE__);

			$data = [
				'name' => $key
			];

			if (!$this->usesBucketPolicyOnly()) {
				$data['predefinedAcl'] = self::GOOGLE_ACL['public-read'];
			}

			$bucket->upload(null, $data);

			Logger::endTiming("End Create Directory", ['file' => $key], __METHOD__, __LINE__);

			return true;
		} catch (\Exception $ex) {
			Logger::error("Error creating directory $key ...",['exception' => $ex->getMessage()], __METHOD__, __LINE__);

			StorageException::ThrowFromOther($ex);
		}

		return false;
	}

	public function deleteDirectory($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$key = trailingslashit($key);
		$files = $this->dir($key, null)['files'];
		/** @var StorageFile $file */
		foreach($files as $file) {
			if ($file->type() == 'FILE') {
				try {
					$this->delete($file->key());
				} catch (\Exception $ex) {
					Logger::error('Google Delete File Error', [
						'exception' => $ex->getMessage(),
						'Bucket' => $this->settings->bucket,
						'Key' => $file->key()
					], __METHOD__, __LINE__);
				}
			}
		}

		/** @var StorageFile $file */
		foreach($files as $file) {
			if ($file->type() == 'DIR') {
				try {
					$this->delete($file->key());
				} catch (\Exception $ex) {
					Logger::error('Google Delete File Error', [
						'exception' => $ex->getMessage(),
						'Bucket' => $this->settings->bucket,
						'Key' => $file->key()
					], __METHOD__, __LINE__);
				}
			}
		}

		try {
			$this->delete($key);
		} catch (\Exception $ex) {
			Logger::error('Google Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			], __METHOD__, __LINE__);
		}
	}

	public function dir($path = '', $delimiter = '/', $limit = -1, $next = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$fileIter = $this->client->bucket($this->settings->bucket)->objects([
			'prefix' => $path,
			'delimiter' => $delimiter
		]);

		$files = [];
		$dirs = [];
		/** @var StorageObject $file */
		foreach($fileIter as $file) {
			if (strpos(strrev($file->name()), '/') === 0) {
				if ($file->name() != $path) {
					$dirs[] = new StorageFile('DIR', $file->name());
				}
				continue;
			}

			$info = $file->info();
			$files[] = new StorageFile('FILE', $file->name(), null, $info['timeCreated'], $info['size'], $this->presignedUrl($file->name()));

		}

		foreach($fileIter->prefixes() as $prefix) {
			$dirs[] = new StorageFile('DIR', $prefix);
		}

		return [
			'next' => null,
			'files' => array_merge($dirs, $files)
		];
	}

	public function ls($path = '', $delimiter = '/', $limit = -1, $next = null, $recursive = false) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$fileIter = $this->client->bucket($this->settings->bucket)->objects([
			'prefix' => $path,
			'delimiter' => $delimiter
		]);

		$files = [];
		/** @var StorageObject $file */
		foreach($fileIter as $file) {
			if (strpos(strrev($file->name()), '/') === 0) {
				continue;
			}

			$files[] = $file->name();
		}

		if (!empty($recursive)) {
			foreach($fileIter->prefixes() as $prefix) {
				$res = $this->ls($prefix, $delimiter, $limit, $next, true);
				if (!empty($res['files'])) {
					$files = array_merge($files, $res['files']);
				}
			}
		}

		return [
			'next' => null,
			'files' => $files
		];
	}
	//endregion

	//region URLs
	public function presignedUrl($key, $expiration = 0, $options = []) {
		if (empty($expiration)) {
			$expiration = $this->settings->presignedURLExpiration;
		}

		$object = $this->client->bucket($this->settings->bucket)->object($key);
		$signedUrl = $object->signedUrl(new Timestamp(new \DateTime("+{$expiration} minutes")));

		if (!empty($options) && is_array($options) && isset($options['ResponseContentDisposition'])) {
			$signedUrl .= '&response-content-disposition='.$options['ResponseContentDisposition'];
		}

		return $signedUrl;
	}

	public function url($key, $type = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		if (($type !== 'skip') && $this->usesSignedURLs($type)) {
			$expiration = $this->signedURLExpirationForType($type);
			return $this->presignedUrl($key, $expiration);
		}

		$object = $this->client->bucket($this->settings->bucket)->object($key);
		$url = $object->gcsUri();
		$url = str_replace('gs://', static::defaultDownloadUrl().'/', $url);
		return $url;
	}
	//endregion

	//region Direct Uploads

	public function uploadUrl($key, $acl, $mimeType=null, $cacheControl = null, $expires = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->settings->bucket);
		$object = $bucket->object($key);

		$options=[];

		if (!empty($mimeType)) {
			$options['contentType'] = $mimeType;
		}

		if (!empty($cacheControl)) {
			$options['cacheControl'] = $cacheControl;
		}

		if (!$this->usesBucketPolicyOnly()) {
			$options['predefinedAcl'] = self::GOOGLE_ACL[$acl];
		}

		$url = $object->signedUploadUrl(new Timestamp(new \DateTime('tomorrow')), $options);

		return new GoogleUploadInfo($key, $url, self::GOOGLE_ACL[$acl]);
	}

	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-direct-upload-google', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-google.js', [], MEDIA_CLOUD_VERSION, true);
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
			$builder = new WizardBuilder('cloud-storage-google', true);
		}

		$builder
			->section('cloud-storage-google', true)
			->select('Getting Started', 'Learn about Google Cloud Storage and how to setup your Google Cloud account to work with Media Cloud.')
				->group('wizard.cloud-storage.providers.google.intro', 'select-buttons')
					->option('read-tutorial', 'Step By Step Tutorial', null, null, 'cloud-storage-s3-tutorial')
				->endGroup()
			->endStep()
			->form('wizard.cloud-storage.providers.google.form', 'Google Cloud Storage Settings', 'Configure Media Cloud with your Google Cloud Storage settings.', [static::class, 'processWizardSettings'])
				->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
				->hiddenField('mcloud-storage-provider', 'google')
				->uploadField('mcloud-storage-google-credentials-file', 'Credentials JSON File', 'The JSON file containing your Google Cloud Storage credentials.', false)
				->textField('mcloud-storage-google-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
				->checkboxField('mcloud-storage-bucket-policy-only', 'Use Bucket Policy Only', "Set to true to when using a bucket which has the 'Bucket Policy Only' flag enabled.  See <a target='_blank' href='https://cloud.google.com/storage/docs/bucket-policy-only'>this documentation</a> for more information.  Also, make sure to make the bucket public, as specified in <a target-'_blank' href='https://cloud.google.com/storage/docs/access-control/making-data-public#buckets'>this documentation</a>.", false)
			->endStep()
			->testStep('wizard.cloud-storage.providers.google.test', 'Test Settings', 'Perform tests to insure that Google Cloud Storage is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.google.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		$builder
			->tutorialSection('cloud-storage-google-tutorial', true)
				->tutorial('wizard.cloud-storage.providers.google.tutorial.step-1', 'Create Role', 'Create a role that defines what capabilities are going to be granted to Media Cloud.')
				->tutorial('wizard.cloud-storage.providers.google.tutorial.step-2', 'Create Service Account', 'Create a service account that allows Media Cloud to interact with Google Cloud Storage.', null, true)
				->tutorial('wizard.cloud-storage.providers.google.tutorial.step-3', 'Create Bucket', 'Create the bucket we’ll be using with Media Cloud.', null, true)
				->tutorial('wizard.cloud-storage.providers.google.tutorial.step-4', 'CORS Configuration (Optional)', 'If you plan on using the Direct Uploads functionality, learn how to configure CORS to make that happen.', null, true)
			->endSection();

		return $builder;
	}

	public static function processWizardSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-storage-settings')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		if (isset($_FILES['mcloud-storage-google-credentials-file'])) {
			$credentials = file_get_contents($_FILES['mcloud-storage-google-credentials-file']['tmp_name']);
		}

		if (empty($credentials)) {
			$credentials = Environment::Option('mcloud-storage-google-credentials');
		}


		$bucket = arrayPath($_POST, 'mcloud-storage-google-bucket', null);
		$bucketPolicyOnly = arrayPath($_POST, 'mcloud-storage-bucket-policy-only', false);

		if (anyNull($credentials, $bucket)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing required fields'], 200);
		}

		$oldProvider = Environment::ReplaceOption('mcloud-storage-provider', 'google');
		$oldCredentials = Environment::ReplaceOption('mcloud-storage-google-credentials', $credentials);
		$oldBucket = Environment::ReplaceOption('mcloud-storage-google-bucket', $bucket);
		$oldBucketPolicyOnly = Environment::ReplaceOption('mcloud-storage-bucket-policy-only', $bucketPolicyOnly);

		try {
			$storage = new GoogleStorage();
			$restoreOld = !$storage->validateSettings();
		} catch (\Exception $ex) {
			$restoreOld = true;
		}

		if ($restoreOld) {
			Environment::UpdateOption('mcloud-storage-provider', $oldProvider);
			Environment::UpdateOption('mcloud-storage-s3-bucket', $oldBucket);
			Environment::UpdateOption('mcloud-storage-google-credentials', $oldCredentials);
			Environment::UpdateOption('mcloud-storage-bucket-policy-only', $oldBucketPolicyOnly);

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
