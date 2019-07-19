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

namespace ILAB\MediaCloud\Storage\Driver\GoogleCloud;

use FasterImage\FasterImage;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use ILAB\MediaCloud\Storage\FileInfo;
use ILAB\MediaCloud\Storage\InvalidStorageSettingsException;
use ILAB\MediaCloud\Storage\StorageConstants;
use ILAB\MediaCloud\Storage\StorageException;
use ILAB\MediaCloud\Storage\StorageFile;
use ILAB\MediaCloud\Storage\StorageInterface;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use League\Flysystem\AdapterInterface;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

class GoogleStorage implements StorageInterface {
	const GOOGLE_ACL = [
		StorageConstants::ACL_PRIVATE_READ => 'authenticatedRead',
		StorageConstants::ACL_PUBLIC_READ => 'publicRead'
	];

	//region Properties
	/*** @var string */
	private $credentials = null;

	/*** @var string */
	private $bucket = null;

	/*** @var bool */
	private $settingsError = false;

	/*** @var StorageClient */
	private $client = null;

    /** @var null|AdapterInterface */
    protected $adapter = null;

	/** @var bool  */
	protected $usePresignedURLs = false;

	/** @var int  */
	protected $presignedURLExpiration = 10;
	//endregion

	//region Constructor
	public function __construct() {
		$this->bucket = Environment::Option('mcloud-storage-google-bucket', [
		    'ILAB_CLOUD_GOOGLE_BUCKET',
			'ILAB_AWS_S3_BUCKET',
			'ILAB_CLOUD_BUCKET'
		]);

		$credFile = Environment::Option(null, 'ILAB_CLOUD_GOOGLE_CREDENTIALS');
		if (!empty($credFile)) {
			if (file_exists($credFile)) {
				$this->credentials = json_decode(file_get_contents($credFile), true);
			} else {
				Logger::error("Credentials file '$credFile' could not be found.");
			}
		}

		if (empty($this->credentials)) {
			$creds = Environment::Option('mcloud-storage-google-credentials');
			if (!empty($creds)) {
				$this->credentials = json_decode($creds, true);
			}
		}

		$this->settingsError = Environment::Option('mcloud-google-settings-error', null, false);

		$this->usePresignedURLs = Environment::Option('mcloud-storage-use-presigned-urls', null, false);
		$this->presignedURLExpiration = Environment::Option('mcloud-storage-presigned-expiration', null, 10);


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
    public function usesSignedURLs() {
        return $this->usePresignedURLs;
    }

	public function supportsDirectUploads() {
		return true;
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
					if($client->bucket($this->bucket)->exists()) {
						$valid = true;
					} else {
                        if ($errorCollector) {
                            $errorCollector->addError("Bucket {$this->bucket} does not exist.");
                        }

						Logger::info("Bucket does not exist.");
					}
                } catch (\Exception $ex) {
                    if ($errorCollector) {
                        $errorCollector->addError("Error insuring that {$this->bucket} exists.  Message: ".$ex->getMessage());
                    }

                    Logger::error("Google Storage Error", ['exception' => $ex->getMessage()]);
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
		if(empty($this->credentials) || (!is_array($this->credentials)) || empty($this->bucket)) {
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
		if (!empty($this->credentials) && is_array($this->credentials)) {
			$client = new StorageClient([
				                                  'projectId' => $this->credentials['project_id'],
				                                  'keyFile' => $this->credentials,
                                                  'scopes' => StorageClient::FULL_CONTROL_SCOPE
			                                  ]);
		}

		if(!$client) {
            if ($errorCollector) {
                $errorCollector->addError("Google configuration is incorrect or missing.");
            }

			Logger::info('Could not create Google storage client.');
		}

		return $client;
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
		$object = $this->client->bucket($this->bucket)->object($key);
		$object->update(['acl' => []], ['predefinedAcl' => self::GOOGLE_ACL[$acl]]);
	}

	public function exists($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->bucket($this->bucket)->object($key)->exists();
	}

	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->bucket);

		$sourceObject = $bucket->object($sourceKey);

		try {
			$sourceObject->copy($bucket,[
				'name' => $destKey,
				'predefinedAcl' => self::GOOGLE_ACL[$acl],
				'metadata'=> [
					'cacheControl' => $cacheControl
				]
			]);
		} catch (\Exception $ex) {
			StorageException::ThrowFromOther($ex);
		}
	}

	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->bucket);

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
			Logger::startTiming("Start Upload", ['file' => $key]);
			$object = $bucket->upload(fopen($fileName, 'r'),[
				'name' => $key,
				'predefinedAcl' => self::GOOGLE_ACL[$acl],
				'metadata'=> $metadata
			]);
			Logger::endTiming("End Upload", ['file' => $key]);
		} catch (\Exception $ex) {
			Logger::error("Error uploading $fileName ...",['exception' => $ex->getMessage()]);

			StorageException::ThrowFromOther($ex);
		}

		$url = $object->gcsUri();
		$url = str_replace('gs://',static::defaultDownloadUrl().'/', $url);

		return $url;
	}

	public function delete($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->bucket);

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

		$object = $this->client->bucket($this->bucket)->object($key);
		$info = $object->info();
		$length = $info['size'];
		$type = $info['contentType'];

		$url = $object->gcsUri();
		$url = str_replace('gs://','https://'.static::defaultDownloadUrl().'/', $url);

		$presignedUrl = $object->signedUrl(new Timestamp(new \DateTime("+{$this->presignedURLExpiration} minutes")));

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

		$bucket = $this->client->bucket($this->bucket);

		try {
			Logger::startTiming("Start Create Directory", ['file' => $key]);
			$object = $bucket->upload(null,[
				'name' => $key,
				'predefinedAcl' => self::GOOGLE_ACL['public-read']
			]);
			Logger::endTiming("End Create Directory", ['file' => $key]);

			return true;
		} catch (\Exception $ex) {
			Logger::error("Error creating directory $key ...",['exception' => $ex->getMessage()]);

			StorageException::ThrowFromOther($ex);
		}

		return false;
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
					Logger::error('Google Delete File Error', [
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
					Logger::error('Google Delete File Error', [
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
			Logger::error('Google Delete File Error', [
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

		$fileIter = $this->client->bucket($this->bucket)->objects([
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

		$fileList = array_merge($dirs, $files);

		return $fileList;
	}

	//endregion

	//region URLs
	public function presignedUrl($key, $expiration = 0) {
		if (empty($expiration)) {
			$expiration = $this->presignedURLExpiration;
		}

		$object = $this->client->bucket($this->bucket)->object($key);
		return $object->signedUrl(new Timestamp(new \DateTime("+{$expiration} minutes")));
	}

	public function url($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		if ($this->usePresignedURLs) {
			return $this->presignedUrl($key);
		}

		$object = $this->client->bucket($this->bucket)->object($key);
		$url = $object->gcsUri();
		$url = str_replace('gs://','https://'.static::defaultDownloadUrl().'/', $url);
		return $url;
	}
	//endregion

	//region Direct Uploads

	public function uploadUrl($key, $acl, $mimeType=null, $cacheControl = null, $expires = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$bucket = $this->client->bucket($this->bucket);
		$object = $bucket->object($key);

		$options=[];

		if (!empty($mimeType)) {
			$options['contentType'] = $mimeType;
		}

		if (!empty($cacheControl)) {
			$options['cacheControl'] = $cacheControl;
		}

		$options['predefinedAcl'] = self::GOOGLE_ACL[$acl];

		$url = $object->signedUploadUrl(new Timestamp(new \DateTime('tomorrow')), $options);

		return new GoogleUploadInfo($key, $url, self::GOOGLE_ACL[$acl]);
	}

	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-direct-upload-google', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-google.js', [], false, true);
	}
	//endregion


    //region Filesystem
    public function adapter() {
        if (!empty($this->adapter)) {
            return $this->adapter;
        }

        $this->adapter = new GoogleStorageAdapter($this->client, $this->client->bucket($this->bucket));
        return $this->adapter;
    }
    //endregion
}
