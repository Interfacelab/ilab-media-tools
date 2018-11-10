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

namespace ILAB\MediaCloud\Cloud\Storage\Driver\Backblaze;

use ChrisWhite\B2\Bucket;
use ChrisWhite\B2\Client;
use FasterImage\FasterImage;
use ILAB\MediaCloud\Cloud\Storage\FileInfo;
use ILAB\MediaCloud\Cloud\Storage\InvalidStorageSettingsException;
use ILAB\MediaCloud\Cloud\Storage\StorageException;
use ILAB\MediaCloud\Cloud\Storage\StorageInterface;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

class BackblazeStorage implements StorageInterface {
	//region Properties
	/*** @var string */
	protected $accountId = null;

	/*** @var string */
	protected $key = null;

	/*** @var string */
	protected $bucket = null;

	/*** @var string */
	protected $bucketUrl = false;

	/*** @var bool */
	protected $settingsError = false;

	/*** @var Client */
	protected $client = null;
	//endregion

	//region Constructor
	public function __construct() {
		$this->bucket = EnvironmentOptions::Option('ilab-media-s3-bucket', [
			'ILAB_AWS_S3_BUCKET',
			'ILAB_CLOUD_BUCKET'
		]);

		$this->key = EnvironmentOptions::Option('ilab-media-backblaze-key', 'ILAB_BACKBLAZE_KEY');
		$this->accountId = EnvironmentOptions::Option('ilab-media-backblaze-account-id', 'ILAB_BACKBLAZE_ACCOUNT_ID');
		$this->bucketUrl = EnvironmentOptions::Option('ilab-media-backblaze-bucket-url', 'ILAB_BACKBLAZE_BUCKET_URL');
		if (!empty($this->bucketUrl)) {
			$host = parse_url($this->bucketUrl, PHP_URL_HOST);
			$this->bucketUrl = "https://{$host}/file/{$this->bucket}/";
		}

		$this->settingsError = get_option('ilab-backblaze-settings-error', false);

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

	public static function bucketLink($bucket) {
		return null;
	}

	public static function pathLink($bucket, $key) {
		return null;
	}
	//endregion

	//region Enabled/Options
    public function usesSignedURLs() {
        return false;
    }

    public function supportsDirectUploads() {
		return false;
	}

    /**
     * @param ErrorCollector|null $errorCollector
     * @return bool|void
     */
	public function validateSettings($errorCollector = null) {
		delete_option('ilab-backblaze-settings-error');
		$this->settingsError = false;

		$this->client = null;
		$valid = false;

		if($this->enabled()) {
			$client = $this->getClient();

			if($client) {
				$buckets = $client->listBuckets();
				foreach($buckets as $bucket) {
					/** @var $bucket Bucket */
					if ($bucket->getName() == $this->bucket) {
						$valid = true;
						break;
					}
				}

				if (!$valid) {
                    if ($errorCollector) {
                        $errorCollector->addError("Unable to find bucket named {$this->bucket}.");
                    }
                }
			}

			if(!$valid) {
				$this->settingsError = true;
				update_option('ilab-backblaze-settings-error', true);
			} else {
				$this->client = $client;
			}
		} else {
            if ($errorCollector) {
                $errorCollector->addError("Backblaze settings are missing or incorrect.");
            }
        }
	}

	public function enabled() {
		if(!($this->key && $this->accountId && $this->bucket && $this->bucketUrl)) {
			NoticeManager::instance()->displayAdminNotice('error', "To start using Cloud Storage, you will need to <a href='admin.php?page=media-tools-s3'>supply your Backblaze credentials.</a>.");

			return false;
		}

		if($this->settingsError) {
			NoticeManager::instance()->displayAdminNotice('error', 'Your Backblaze settings are incorrect or the bucket does not exist.  Please verify your settings and update them.');

			return false;
		}

		return true;
	}
	//endregion

	//region Client Creation

	/**
	 * Returns the Backblaze client
	 * @return Client
	 */
	protected function getClient() {
		if(!$this->enabled()) {
			return null;
		}

		return new Client($this->accountId, $this->key);
	}
	//endregion

	//region File Functions

	public function bucket() {
		return $this->bucket;
	}

	public function region() {
		return null;
	}

	public function insureACL($key, $acl) {
	}

	public function exists($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$fileId = $this->mapKeyToFileId($key);
		if (empty($fileId)) {
			return false;
		}

		return $this->client->fileExists(['BucketName' => $this->bucket, 'FileName' => $key]);
	}

	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
	}

	public function upload($key, $fileName, $acl, $cacheControl = false, $expires = false) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			Logger::startTiming("Start Upload", ['file' => $key]);
			$this->client->upload([
				                      'BucketName' => $this->bucket,
				                      'FileName' => $key,
				                      'Body' => fopen($fileName, 'r')
			                      ]);
			Logger::endTiming("End Upload", ['file' => $key]);

			return $this->bucketUrl.$key;
		} catch (\Exception $ex) {
			Logger::error("Backblaze upload error", ['exception' => $ex->getMessage()]);
			StorageException::ThrowFromOther($ex);
		}
	}

	public function delete($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$this->client->deleteFile(['BucketName' => $this->bucket, 'FileName' => $key]);
		} catch(\Exception $ex) {
			Logger::error('Backblaze Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->bucket,
				'Key' => $key
			]);
		}
	}

	public function info($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$file = $this->client->getFile(['BucketName' => $this->bucket, 'FileName' => $key]);
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

		$fileInfo = new FileInfo($key, $url, $length, $type, $size);

		return $fileInfo;
	}
	//endregion

	//region URLs
	public function presignedUrl($key) {
		return $this->bucketUrl.$key;
	}

	public function url($key) {
		return $this->bucketUrl.$key;
	}
	//endregion

	//region Direct Uploads
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
	}

	public function enqueueUploaderScripts() {
	}
	//endregion
}
