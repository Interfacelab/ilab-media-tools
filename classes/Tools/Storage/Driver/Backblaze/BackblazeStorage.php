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

namespace MediaCloud\Plugin\Tools\Storage\Driver\Backblaze;

use MediaCloud\Vendor\ILAB\B2\Bucket;
use MediaCloud\Vendor\ILAB\B2\Client;
use MediaCloud\Vendor\ILAB\B2\Exceptions\B2Exception;
use MediaCloud\Vendor\FasterImage\FasterImage;
use MediaCloud\Vendor\ILAB\B2\AuthCacheInterface;
use MediaCloud\Vendor\ILAB\B2\File;
use MediaCloud\Plugin\Tools\Storage\FileInfo;
use MediaCloud\Plugin\Tools\Storage\InvalidStorageSettingsException;
use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageFile;
use MediaCloud\Plugin\Tools\Storage\StorageInterface;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
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

class BackblazeStorage implements StorageInterface, AuthCacheInterface, ConfiguresWizard {
	use StorageWizardTrait;

	//region Properties
	/** @var BackblazeSettings|null  */
	protected $settings = null;

	/** @var string|null */
	protected $bucketUrl = null;

	/** @var Client|null */
	protected $client = null;
	//endregion

	//region Constructor
	public function __construct() {
		$this->settings = new BackblazeSettings();
		$this->client = $this->getClient();
	}
	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'backblaze';
	}

	public static function name() {
		return 'Backblaze B2';
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

	public static function forcedRegion() {
		return null;
	}

	public static function bucketLink($bucket) {
		return null;
	}

	public function pathLink($bucket, $key) {
		return null;
	}
	//endregion

	//region Enabled/Options
    public function usesSignedURLs($type = null) {
        return false;
    }

    public function supportsDirectUploads() {
		return false;
	}

	public function supportsWildcardDirectUploads() {
		return false;
	}

	public function supportsBrowser() {
		return true;
	}

	/**
     * @param ErrorCollector|null $errorCollector
     * @return bool|void
     */
	public function validateSettings($errorCollector = null) {
		delete_option('ilab-backblaze-settings-error');
		$this->settings->settingsError = false;

		$this->client = null;
		$valid = false;

		if($this->enabled()) {
			$client = $this->getClient();

			if($client) {
				$buckets = $client->listBuckets();
				foreach($buckets as $bucket) {
					/** @var $bucket Bucket */
					if ($bucket->getName() == $this->settings->bucket) {
						$valid = true;
						break;
					}
				}

				if (!$valid) {
                    if ($errorCollector) {
                        $errorCollector->addError("Unable to find bucket named {$this->settings->bucket}.");
                    }
                }
			}

			if(!$valid) {
				$this->settings->settingsError = true;
				update_option('ilab-backblaze-settings-error', true);
			} else {
				$this->client = $client;
			}
		} else {
            if ($errorCollector) {
                $errorCollector->addError("Backblaze settings are missing or incorrect.");
            }
        }

		return $valid;
	}

	public function settings() {
		return $this->settings;
	}

	public function enabled() {
		if(!($this->settings->key && $this->settings->accountId && $this->settings->bucket)) {
            $adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
			NoticeManager::instance()->displayAdminNotice('error', "To start using Cloud Storage, you will need to <a href='$adminUrl'>supply your Backblaze credentials.</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');

			return false;
		}

		if($this->settings->settingsError) {
			NoticeManager::instance()->displayAdminNotice('error', 'Your Backblaze settings are incorrect or the bucket does not exist.  Please verify your settings and update them.');

			return false;
		}

		return true;
	}

	public function settingsError() {
		return $this->settings->settingsError;
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
	 * Returns the Backblaze client
	 *
	 * @return Client|null
	 */
	protected function getClient() {
		if(!$this->enabled()) {
			return null;
		}

		try {
            $client = new Client($this->settings->accountId, $this->settings->key, [], $this);

            $this->bucketUrl = $client->downloadUrl().'/file/'.$this->settings->bucket.'/';

            return $client;
        } catch (B2Exception $ex) {
            $this->settings->settingsError = true;
            update_option('ilab-backblaze-settings-error', true);
        }
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
	}

	public function updateACL($key, $acl) {
	}

	public function canUpdateACL() {
		return false;
	}

	public function exists($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->fileExists(['BucketName' => $this->settings->bucket, 'FileName' => $key]);
	}

	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
	}

	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null, $tries = 1) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			Logger::startTiming("Start Upload", ['file' => $key], __METHOD__, __LINE__);
			$this->client->upload([
				                      'BucketName' => $this->settings->bucket,
				                      'FileName' => $key,
				                      'Body' => fopen($fileName, 'r')
			                      ]);
			Logger::endTiming("End Upload", ['file' => $key], __METHOD__, __LINE__);

			return $this->bucketUrl.$key;
		} catch (\Exception $ex) {
			Logger::error("Backblaze upload error", ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
			if ($tries < 4) {
			    return $this->upload($key, $fileName, $acl, $cacheControl, $expires, null, null, null, $tries + 1);
            } else {
                StorageException::ThrowFromOther($ex);
            }
		}
	}

	public function delete($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$this->client->deleteFile(['BucketName' => $this->settings->bucket, 'FileName' => $key]);
		} catch(\Exception $ex) {
			Logger::error('Backblaze Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			], __METHOD__, __LINE__);
		}
	}

	public function createDirectory($key) {
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
					Logger::error('Backblaze Delete File Error', [
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
					Logger::error('Backblaze Delete File Error', [
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
			Logger::error('Backblaze Delete File Error', [
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

		/** @var File[] $files */
		$storageFiles = $this->client->listFiles([
			'BucketName' => $this->settings->bucket,
			'prefix' => $path,
			'delimiter' => $delimiter
		]);

		$dirs = [];
		$files = [];
		foreach($storageFiles as $file) {
			if ($file->getType() == 'folder') {
				$dirs[] = new StorageFile('DIR', $file->getName(), null, $file->getUploadTimestamp(), $file->getSize());
			} else {
				$files[] = new StorageFile('FILE', $file->getName(), null, $file->getUploadTimestamp(), $file->getSize(), $this->presignedUrl($file->getName()));
			}
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

		/** @var File[] $files */
		$storageFiles = $this->client->listFiles([
			'BucketName' => $this->settings->bucket,
			'prefix' => $path,
			'delimiter' => $delimiter
		]);

		$files = [];
		foreach($storageFiles as $file) {
			if ($file->getType() != 'folder') {
				$files[] = $file->getName();
			}
		}

		return [
			'next' => null,
			'files' => $files
		];
	}

	public function info($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$file = $this->client->getFile(['BucketName' => $this->settings->bucket, 'FileName' => $key]);
			$length = $file->getSize();
			$type = $file->getType();
		}
		catch(AwsException $ex) {
			throw new StorageException($ex->getMessage(), $ex->getStatusCode(), $ex);
		}

		$url = $this->bucketUrl.$key;

		$size = null;
		if(strpos($type, 'image/') === 0) {
			$faster = new FasterImage();
			$result = $faster->batch([$url]);
			$result = $result[$url];
			$size = $result['size'];
		}

		$fileInfo = new FileInfo($key, $url, $url, $length, $type, $size);

		return $fileInfo;
	}
	//endregion

	//region URLs
	public function presignedUrl($key, $expiration = 0, $options = []) {
		return $this->bucketUrl.$key;
	}

	public function url($key, $type = null) {
		return $this->bucketUrl.$key;
	}

	public function signedURLExpirationForType($type = null) {
		return null;
	}
	//endregion

	//region Direct Uploads
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
	}

	public function enqueueUploaderScripts() {
	}
	//endregion

    //region Auth Cache
    public function cacheB2Auth($key, $authData) {
	    update_option('ilab_media_cloud_b2_auth_cache', [
            'key' => $key,
            'time' => time(),
            'data' => $authData
        ]);
    }

    public function cachedB2Auth($key) {
	    $authData = get_option('ilab_media_cloud_b2_auth_cache', null);
	    if (empty($authData)) {
	        return null;
        }

        if ($authData['key'] != $key) {
            return null;
        }

        if (time() > ($authData['time'] + (60 * 60 * 23))) {
            delete_option('ilab_media_cloud_b2_auth_cache');
            return null;
        }

        return $authData['data'];
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
			$builder = new WizardBuilder('cloud-storage-backblaze', true);
		}

		$builder
			->section('cloud-storage-backblaze', true)
				->select('Getting Started', 'Learn about Backblaze and how to set it up to work with Media Cloud.')
					->group('wizard.cloud-storage.providers.backblaze.intro', 'select-buttons')
						->option('read-tutorial', 'Step By Step Tutorial', null, null, 'cloud-storage-backblaze-tutorial')
					->endGroup()
				->endStep()
				->form('wizard.cloud-storage.providers.backblaze.form', 'Cloud Storage Settings', 'Configure Media Cloud with your cloud storage settings.', [static::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 'backblaze')
					->textField('mcloud-storage-backblaze-account-id', 'Account Id or Key Id', '', null)
					->passwordField('mcloud-storage-backblaze-key', 'Key', '', null)
					->textField('mcloud-storage-s3-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
				->endStep()
				->testStep('wizard.cloud-storage.providers.backblaze.test', 'Test Settings', 'Perform tests to insure that your cloud storage provider is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.backblaze.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

		$builder
			->tutorialSection('cloud-storage-backblaze-tutorial', true)
				->tutorial('wizard.cloud-storage.providers.backblaze.tutorial.step-1', 'Create Bucket', 'Create the bucket you will be using with Media Cloud.')
				->tutorial('wizard.cloud-storage.providers.backblaze.tutorial.step-2', 'Create Application Key', 'Generate the API keys Media Cloud uses to access Backblaze.', null, true)
		->endSection();

		return $builder;
	}

	public static function processWizardSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-storage-settings')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		$providerName = 'mcloud-storage-provider';
		$accessKeyName = 'mcloud-storage-backblaze-account-id';
		$secretName = 'mcloud-storage-backblaze-key';
		$bucketName = 'mcloud-storage-s3-bucket';

		$provider = arrayPath($_POST, $providerName, null);
		$accessKey = arrayPath($_POST, $accessKeyName, null);
		$secret = arrayPath($_POST, $secretName, null);
		$bucket = arrayPath($_POST, $bucketName, null);

		if (anyNull($provider, $accessKey, $secret, $bucket)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing required fields'], 200);
		}

		$oldProvider = Environment::ReplaceOption($providerName, $provider);
		$oldBucket = Environment::ReplaceOption($bucketName, $bucket);
		$oldKey = Environment::ReplaceOption($accessKeyName, $accessKey);
		$oldSecret = Environment::ReplaceOption($secretName, $secret);

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
