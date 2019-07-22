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

namespace ILAB\MediaCloud\Storage\Driver\Backblaze;

use ILAB\B2\Bucket;
use ILAB\B2\Client;
use ILAB\B2\Exceptions\B2Exception;
use FasterImage\FasterImage;
use ILAB\B2\AuthCacheInterface;
use ILAB\B2\File;
use ILAB\MediaCloud\Storage\FileInfo;
use ILAB\MediaCloud\Storage\InvalidStorageSettingsException;
use ILAB\MediaCloud\Storage\StorageException;
use ILAB\MediaCloud\Storage\StorageFile;
use ILAB\MediaCloud\Storage\StorageInterface;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

class BackblazeStorage implements StorageInterface, AuthCacheInterface {
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
		$this->bucket = Environment::Option('mcloud-storage-s3-bucket', [
			'ILAB_AWS_S3_BUCKET',
			'ILAB_CLOUD_BUCKET'
		]);

		$this->key = Environment::Option('mcloud-storage-backblaze-key', 'ILAB_BACKBLAZE_KEY');
		$this->accountId = Environment::Option('mcloud-storage-backblaze-account-id', 'ILAB_BACKBLAZE_ACCOUNT_ID');

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
    public function usesSignedURLs() {
        return false;
    }

    public function supportsDirectUploads() {
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

		return $valid;
	}

	public function enabled() {
		if(!($this->key && $this->accountId && $this->bucket)) {
            $adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
			NoticeManager::instance()->displayAdminNotice('error', "To start using Cloud Storage, you will need to <a href='$adminUrl'>supply your Backblaze credentials.</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');

			return false;
		}

		if($this->settingsError) {
			NoticeManager::instance()->displayAdminNotice('error', 'Your Backblaze settings are incorrect or the bucket does not exist.  Please verify your settings and update them.');

			return false;
		}

		return true;
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
	 * @return Client
	 */
	protected function getClient() {
		if(!$this->enabled()) {
			return null;
		}

		try {
            $client = new Client($this->accountId, $this->key, [], $this);

            $this->bucketUrl = $client->downloadUrl().'/file/'.$this->bucket.'/';

            return $client;
        } catch (B2Exception $ex) {
            $this->settingsError = true;
            update_option('ilab-backblaze-settings-error', true);
        }
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

	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null, $tries = 1) {
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
			$this->client->deleteFile(['BucketName' => $this->bucket, 'FileName' => $key]);
		} catch(\Exception $ex) {
			Logger::error('Backblaze Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->bucket,
				'Key' => $key
			]);
		}
	}

	public function createDirectory($key) {
	}

	public function deleteDirectory($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$key = trailingslashit($key);
		$files = $this->dir($key, null);
		/** @var StorageFile $file */
		foreach($files as $file) {
			if ($file->type() == 'FILE') {
				try {
					$this->delete($file->key());
				} catch (\Exception $ex) {
					Logger::error('Backblaze Delete File Error', [
						'exception' => $ex->getMessage(),
						'Bucket' => $this->bucket,
						'Key' => $file->key()
					]);
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
						'Bucket' => $this->bucket,
						'Key' => $file->key()
					]);
				}
			}
		}

		try {
			$this->delete($key);
		} catch (\Exception $ex) {
			Logger::error('Backblaze Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->bucket,
				'Key' => $key
			]);
		}
	}

	public function dir($path = '', $delimiter = '/') {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		/** @var File[] $files */
		$storageFiles = $this->client->listFiles([
			'BucketName' => $this->bucket,
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

		return array_merge($dirs, $files);
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

		$fileInfo = new FileInfo($key, $url, $url, $length, $type, $size);

		return $fileInfo;
	}
	//endregion

	//region URLs
	public function presignedUrl($key, $expiration = 0) {
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

    //region Adapter
    public function adapter() {
	    return new BackblazeAdapter($this->client, $this->bucket);
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
}
