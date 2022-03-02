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
use MediaCloud\Plugin\Tools\Storage\StorageFile;
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
use MediaCloud\Vendor\Aws\CloudFront\CloudFrontClient;
use MediaCloud\Vendor\Aws\Credentials\CredentialProvider;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\Exception\CredentialsException;
use MediaCloud\Vendor\Aws\S3\PostObjectV4;
use MediaCloud\Vendor\Aws\S3\S3Client;
use MediaCloud\Vendor\Aws\S3\S3MultiRegionClient;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

class S3Storage implements S3StorageInterface, ConfiguresWizard {
	use StorageWizardTrait;

	//region Properties
	/** @var S3StorageSettings|null  */
	protected $settings = null;

	/*** @var S3Client|S3MultiRegionClient|null */
	protected $client = null;

	//endregion

	//region Constructor
	public function __construct() {
		$this->settings = new S3StorageSettings($this);
		$this->client = $this->getClient(null);
	}
	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 's3';
	}

	public static function name() {
		return 'Amazon S3';
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
		return "https://console.aws.amazon.com/s3/buckets/$bucket";
	}

	public function pathLink($bucket, $key) {
		return "https://s3.console.aws.amazon.com/s3/object/{$bucket}/{$key}?region={$this->settings->region}&tab=overview";
	}
	//endregion
	
	//region Enabled/Options
    public function usesSignedURLs($type = null) {
		if (($type === null) || (!empty($this->settings->usePresignedURLs))) {
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

		if (!empty($use) && ($use !== 'inherit')) {
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

	    if (strpos($type, 'application') === 0) {
		    return (empty($this->settings->presignedURLExpirationForDocs)) ? $this->settings->presignedURLExpiration : $this->settings->presignedURLExpirationForDocs;
	    }

	    return $this->settings->presignedURLExpiration;
    }

    public function supportsDirectUploads() {
		return (StorageToolSettings::driver() === 's3');
	}

	public function supportsWildcardDirectUploads() {
		return in_array(StorageToolSettings::driver(), ['s3', 'do']);
	}

	public static function settingsErrorOptionName() {
		return 'ilab-s3-settings-error';
	}

	public function supportsBrowser() {
		return true;
	}

    /**
     * @param ErrorCollector|null $errorCollector
     * @return bool
     */
    public function validateSettings($errorCollector = null) {
		Environment::DeleteOption(static::settingsErrorOptionName());
		$this->settings->settingsError = false;

        $valid = false;
		$this->client = null;
		if($this->enabled()) {
			$client = $this->getClient($errorCollector);

			if (!empty($client)) {
				$connectError = false;

				try {
					if($client->doesBucketExist($this->settings->bucket)) {
						$valid = true;
					}
				} catch (\Exception $ex) {
					$connectError = true;
					$valid = false;

					if ($errorCollector) {
						if ($ex instanceof CredentialsException) {
							if ($this->settings->useCredentialProvider) {
								$errorCollector->addError("Your Amazon credentials are invalid.  You have enabled <strong>Use Credential Provider</strong> but it appears that isn't configured properly.  Either turn off that setting and provide an access key and secret, or double check your credential provider setup.");
							} else {
								$errorCollector->addError("Your Amazon credentials are invalid.  Verify that the access key, secret and bucket name are correct and try this test again.");
							}
						} else {
							$errorCollector->addError("Error attempting to validate client settings.  The error was: ".$ex->getMessage());
						}
					}

					Logger::error("Error insuring bucket exists.", ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
				}

				if (empty($valid) && empty($connectError)) {
					try {
						Logger::warning("Bucket does not exist, trying to list buckets.", [], __METHOD__, __LINE__);

						$result = $client->listBuckets();
						$buckets = $result->get('Buckets');
						if(!empty($buckets)) {
							foreach($buckets as $bucket) {
								if($bucket['Name'] == $this->settings->bucket) {
									$valid = true;
									break;
								}
							}
						}

						if (!$valid) {
                            if ($errorCollector) {
                                $errorCollector->addError("Bucket {$this->settings->bucket} does not exist.");
                            }

                            Logger::error("Bucket does not exist.", [], __METHOD__, __LINE__);
                        }
					} catch(AwsException $ex) {
                        if ($errorCollector) {
                        	$adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
	                        if ($ex->getAwsErrorCode() === 'SignatureDoesNotMatch') {
		                        $errorCollector->addError("Your S3 credentials are invalid.  It appears that the <strong>Secret</strong> you specified is invalid.  Please double check <a href='$adminUrl'>your settings</a>.");
	                        } else if ($ex->getAwsErrorCode() === 'InvalidAccessKeyId') {
		                        $errorCollector->addError("Your S3 credentials are invalid.  It appears that the <strong>Access Key</strong> you specified is invalid.  Please double check <a href='$adminUrl'>your settings</a>.");
	                        } else if ($ex->getAwsErrorCode() === 'AccessDenied') {
		                        $errorCollector->addError("The <strong>Bucket</strong> you specified doesn't exist or you don't have access to it.  You may have also specified the wrong <strong>Region</strong>.  It's also possible that you don't have the correct permissions specified in your IAM policy.  Please set the <strong>Region</strong> to <strong>Automatic</strong> and double check the <strong>Bucket Name</strong> in <a href='$adminUrl'>your settings</a>.");
	                        } else {
		                        $errorCollector->addError($ex->getAwsErrorMessage());
	                        }
                        }

                        Logger::error("Error insuring bucket exists.", ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
					}
				}
			}

			if(!$valid) {
				$this->settings->settingsError = true;
			} else {
				$this->client = $client;
			}
		} else {
            if ($errorCollector) {
                $errorCollector->addError("Account ID, account secret and/or the bucket are incorrect or missing.");
            }
        }

		return $valid;
	}

	public function enabled() {
		if (!((($this->settings->key && $this->settings->secret) || $this->settings->useCredentialProvider) && $this->settings->bucket)) {
			$adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
			NoticeManager::instance()->displayAdminNotice('info', "Welcome to Media Cloud!  To get started, <a href='$adminUrl'>configure your cloud storage</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');

			return false;
		}

		if ($this->settings->settingsError) {
			return false;
		}

		return true;
	}

	public function settingsError() {
    	return $this->settings->settingsError;
	}

	public function settings() {
    	return $this->settings;
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
	 * Attempts to determine the region for the bucket
	 * @return bool|string
	 */
	protected function getBucketRegion() {
		if(!$this->enabled()) {
			return false;
		}

		if($this->settings->useCredentialProvider) {
			$config = [
				'version' => 'latest',
				'credentials' => CredentialProvider::defaultProvider()
			];
		} else {
			$config = [
				'version' => 'latest',
				'credentials' => [
					'key' => $this->settings->key,
					'secret' => $this->settings->secret
				]
			];
		}

		if(!empty($this->settings->endpoint)) {
			$config['endpoint'] = $this->settings->endpoint;
			if($this->settings->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		$s3 = new S3MultiRegionClient($config);
		$region = false;
		try {
			$region = $s3->determineBucketRegion($this->settings->bucket);
		}
		catch(AwsException $ex) {
			Logger::error("AWS Error fetching region", ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
		}

		return $region;
	}

	/**
	 * Builds and returns an S3MultiRegionClient
	 * @return S3MultiRegionClient|null
	 */
	protected function getS3MultiRegionClient() {
		if(!$this->enabled()) {
			return null;
		}

		if($this->settings->useCredentialProvider) {
			$config = [
				'version' => 'latest',
				'credentials' => CredentialProvider::defaultProvider()
			];
		} else {
			$config = [
				'version' => 'latest',
				'credentials' => [
					'key' => $this->settings->key,
					'secret' => $this->settings->secret
				]
			];
		}

		if(!empty($this->settings->endpoint)) {
			$config['endpoint'] = $this->settings->endpoint;

			if($this->settings->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		if($this->settings->useTransferAcceleration && (StorageToolSettings::driver() === 's3')) {
			$config['use_accelerate_endpoint'] = true;
		}

		$s3 = new S3MultiRegionClient($config);
		return $s3;
	}

	/**
	 * Attempts to build the S3Client.  This requires a region be defined or determinable.
	 *
     * @param bool $region
     * @param ErrorCollector|null $errorCollector
	 *
	 * @return S3Client|null
	 */
	protected function getS3Client($region = false, $errorCollector = null) {
		if(!$this->enabled()) {
			return null;
		}

		if(empty($region)) {
			if(empty($this->settings->region)) {
				$this->settings->region = $this->getBucketRegion();
				if(empty($this->settings->region)) {
                    if ($errorCollector) {
                        $errorCollector->addError('Could not determine region.');
                    }

					return null;
				}

				Environment::UpdateOption('mcloud-storage-s3-region', $this->settings->region);
			}

			$region = $this->settings->region;
		}

		if(empty($region)) {
            if ($errorCollector) {
                $errorCollector->addError("Could not determine region or the region was not specified.");
            }

			return null;
		}

		if($this->settings->useCredentialProvider) {
			$config = [
				'version' => 'latest',
				'credentials' => CredentialProvider::defaultProvider(),
				'region' => $region
			];
		} else {
			$config = [
				'version' => 'latest',
				'credentials' => [
					'key' => $this->settings->key,
					'secret' => $this->settings->secret
				],
				'region' => $region
			];
		}

		if(!empty($this->settings->endpoint)) {
			$config['endpoint'] = $this->settings->endpoint;
			if($this->settings->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		if($this->settings->useTransferAcceleration && (StorageToolSettings::driver() === 's3')) {
			$config['use_accelerate_endpoint'] = true;
		}

		$s3 = new S3Client($config);
		return $s3;
	}


    /**
     * Gets the S3Client
     * @param ErrorCollector|null $errorCollector
     * @return S3Client|S3MultiRegionClient|null
     */
	protected function getClient($errorCollector = null) {
		if(!$this->enabled()) {
            if ($errorCollector) {
                $errorCollector->addError("Account ID, account secret and/or the bucket are incorrect or missing.");
            }

			return null;
		}

		$s3 = $this->getS3Client(false, $errorCollector);
		if(!$s3) {
            if ($errorCollector) {
                $errorCollector->addError("Could not create regular client, creating multi-region client instead.");
            }

            $s3 = $this->getS3MultiRegionClient();

            if (!$s3 && $errorCollector) {
                $errorCollector->addError("Could not create regular client, creating multi-region client instead.");
            }
		}

		return $s3;
	}
	//endregion

	//region File Functions
	public function bucket() {
		return $this->settings->bucket;
	}

	public function region() {
		return $this->settings->region;
	}

	public function isUsingPathStyleEndPoint() {
		if (in_array(static::identifier(), ['s3', 'google', 'backblaze', 'wasabi'])) {
			return false;
		}

		return $this->settings->endPointPathStyle;
	}

	public function insureACL($key, $acl) {

	}

	public function acl($key) {
		try {
			$result = $this->client->getObjectAcl([
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			]);

			$grants = arrayPath($result, 'Grants', []);
			foreach($grants as $grant) {
				if (arrayPath($grant, 'Grantee/URI') === 'http://acs.amazonaws.com/groups/global/AllUsers') {
					return (arrayPath($grant, 'Permission') === 'READ') ? 'public-read' : 'private';
				}

				if (arrayPath($grant, 'Grantee/URI') === 'http://acs.amazonaws.com/groups/global/AuthenticatedUsers') {
					return (arrayPath($grant, 'Permission') === 'READ') ? 'authenticated-read' : 'private';
				}
			}

			return null;
		} catch (\Exception $ex) {
			Logger::error("Error fetching ACL for '$key'.  Exception: ".$ex->getMessage(), [], __METHOD__, __LINE__);
			return null;
		}
	}

	public function updateACL($key, $acl) {
		try {
			$result = $this->client->putObjectAcl([
				'ACL' => $acl,
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			]);

			return $result;
		} catch (\Exception $ex) {
			Logger::error("Error changing ACL for '$key' to '$acl'.  Exception: ".$ex->getMessage(), [], __METHOD__, __LINE__);
			return false;
		}
	}

	public function canUpdateACL() {
		return true;
	}

	public function exists($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->doesObjectExist($this->settings->bucket, $key);
	}

	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$copyOptions = [
			'MetadataDirective' => 'REPLACE',
			'Bucket' => $this->settings->bucket,
			'Key' => $destKey,
			'CopySource' => $this->settings->bucket.'/'.$sourceKey,
			'ACL' => $acl
		];

		if($cacheControl) {
			$copyOptions['CacheControl'] = $cacheControl;
		}

		if($expires) {
			$copyOptions['Expires'] = $expires;
		}

		if($mime) {
			$copyOptions['ContentType'] = $mime;
		}

		try {
			$this->client->copyObject($copyOptions);
		}
		catch(AwsException $ex) {
			Logger::error('S3 Error Copying Object', ['exception' => $ex->getMessage(), 'options' => $copyOptions], __METHOD__, __LINE__);
			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$params = [];
		$options = [];

		if($cacheControl) {
			$params['CacheControl'] = $cacheControl;
		}

		if($expires) {
			$params['Expires'] = $expires;
		}

		if ($contentType) {
			$params['ContentType'] = $contentType;
		}

		if ($contentEncoding) {
			$params['ContentEncoding'] = $contentEncoding;
		}

		if ($contentLength) {
			$params['ContentLength'] = $contentLength;
		}

		if(!empty($params)) {
			$options['params'] = $params;
		}

		try {
			$file = fopen($fileName, 'r');

			if (empty($file)) {
				Logger::warning("Unable to open file for upload: $fileName", [], __METHOD__, __LINE__);
				throw new StorageException("File to upload does not exist: $fileName");
			}

			Logger::startTiming("Start Upload", ['file' => $key], __METHOD__, __LINE__);
			$result = $this->client->upload($this->settings->bucket, $key, $file, $acl, $options);
			Logger::endTiming("End Upload", ['file' => $key], __METHOD__, __LINE__);

			if (is_resource($file)) {
				fclose($file);
			}

            return $result->get('ObjectURL');
		}
		catch(AwsException $ex) {
			fclose($file);
			Logger::error('S3 Upload Error', [
				'exception' => $ex->getMessage(),
				'bucket' => $this->settings->bucket,
				'key' => $key,
				'privacy' => $acl,
				'options' => $options
			], __METHOD__, __LINE__);

			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function delete($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			Logger::startTiming("Deleting '$key'", [], __METHOD__, __LINE__);
			$this->client->deleteObject(['Bucket' => $this->settings->bucket, 'Key' => $key]);
			Logger::endTiming("Deleted '$key'", [], __METHOD__, __LINE__);
		}
		catch(AwsException $ex) {
			Logger::error('S3 Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			], __METHOD__, __LINE__);
		}
	}

	public function deleteDirectory($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			Logger::startTiming("Deleting directory '$key'", [], __METHOD__, __LINE__);
			$this->client->deleteMatchingObjects($this->settings->bucket, $key);
			Logger::endTiming("Deleted directory '$key'", [], __METHOD__, __LINE__);
		}
		catch(AwsException $ex) {
			Logger::error('S3 Delete File Error: '.$ex->getMessage(), [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			], __METHOD__, __LINE__);
		}
	}


	public function createDirectory($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$key = rtrim($key, '/').'/';

			Logger::startTiming("Creating folder '$key'", [], __METHOD__, __LINE__);
			$this->client->putObject([
				'Bucket' => $this->settings->bucket,
				'Key' => $key,
				'Body' => '',
				'ACL' => 'public-read',
				'ContentLength' => 0
			]);
			Logger::endTiming("Created folder '$key'", [], __METHOD__, __LINE__);

			return true;
		}
		catch(AwsException $ex) {
			Logger::error('S3 Delete File Error', [
				'exception' => $ex->getMessage(),
				'Bucket' => $this->settings->bucket,
				'Key' => $key
			], __METHOD__, __LINE__);
		}

		return false;
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
			try {
				$result = $faster->batch([$presignedUrl]);
				$result = $result[$presignedUrl];
				$size = $result['size'];
			} catch (\Exception $ex) {
				Logger::error("Error getting file size info for: ".$ex->getMessage(), [], __METHOD__, __LINE__);
			}
		}

		$fileInfo = new FileInfo($key, $url, $presignedUrl, $length, $type, $size);

		return $fileInfo;
	}

	public function dir($path = '', $delimiter = '/', $limit = -1, $next = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$foundDirs = [];
		$contents = [];
		try {
			$args =  [
				'Bucket' => $this->settings->bucket,
				'Prefix' => $path
			];

			if (!empty($delimiter)) {
				$args['Delimiter'] = $delimiter;
			}

			if ($limit !== -1) {
				$args['MaxKeys'] = $limit;
			}

			if (!empty($next)) {
				$args['ContinuationToken'] = $next;
			}

			$results  = $this->client->getPaginator('ListObjectsV2', $args);

			$result = $results->current();
			if (empty($result)) {
				return [
					'next' => null,
					'files' => []
				];
			}

			if (!empty($result['CommonPrefixes'])) {
				foreach($result['CommonPrefixes'] as $prefix) {
					$decodedPrefix = urldecode($prefix['Prefix']);
					if (in_array($decodedPrefix, $foundDirs)) {
						continue;
					}

					$foundDirs[] = $decodedPrefix;
					$contents[] = new StorageFile('DIR', $decodedPrefix);
				}
			}

			if (!empty($result['Contents'])) {
				foreach($result['Contents'] as $object) {
					if ($object['Key'] == $path) {
						continue;
					}

					$contents[] = new StorageFile('FILE', $object['Key'], null, $object['LastModified'], intval($object['Size']), $this->presignedUrl($object['Key']));
				}
			}

			return [
				'next' => isset($result['NextContinuationToken']) ? $result['NextContinuationToken'] : null,
				'files' => $contents
			];
		} catch(AwsException $ex) {
			Logger::error("Error listing objects for $path: ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}

		return [
			'next' => null,
			'files' => []
		];
	}

	public function ls($path = '', $delimiter = '/', $limit = -1, $next = null, $recursive = false) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$contents = [];
		try {
			$args =  [
				'Bucket' => $this->settings->bucket,
				'Prefix' => $path
			];

			if (!empty($delimiter)) {
				$args['Delimiter'] = $delimiter;
			}

			if ($limit !== -1) {
				$args['MaxKeys'] = $limit;
			}

			if (!empty($next)) {
				$args['ContinuationToken'] = $next;
			}

			$results  = $this->client->getPaginator('ListObjectsV2', $args);

			$result = $results->current();
			if (empty($recursive) && (empty($result) || empty($result['Contents']))) {
				return [
					'next' => null,
					'files' => []
				];
			}

			if (!empty($result['Contents'])) {
				foreach($result['Contents'] as $object) {
					if ($object['Key'] == $path) {
						continue;
					}

					$contents[] = $object['Key'];
				}
			}

			if (!empty($recursive)) {
				$recursiveDirs = [];
				if (!empty($result['CommonPrefixes'])) {
					foreach($result['CommonPrefixes'] as $prefix) {
						$decodedPrefix = urldecode($prefix['Prefix']);
						if (in_array($decodedPrefix, $recursiveDirs)) {
							continue;
						}

						$recursiveDirs[] =  $decodedPrefix;
					}
				}

				foreach($recursiveDirs as $recursiveDir) {
					$ls = $this->ls($recursiveDir, $delimiter, $limit, $next, true);
					if (!empty($ls['files'])) {
						$contents = array_merge($contents, $ls['files']);
					}
				}
			}


			return [
				'next' => isset($result['NextContinuationToken']) ? $result['NextContinuationToken'] : null,
				'files' => $contents
			];
		} catch(AwsException $ex) {
			Logger::error("Error listing objects for $path: ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}

		return [
			'next' => null,
			'files' => []
		];
	}
	//endregion

	//region URLs
	protected function presignedRequest($key, $expiration = 0, $options = []) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		if (empty($expiration)) {
			$expiration = $this->settings->presignedURLExpiration;
		}

		if (empty($expiration)) {
			$expiration = 1;
		}

		$commandOptions = [
			'Bucket' => $this->settings->bucket,
			'Key' => $key
		];

		if (!empty($options) && is_array($options)) {
			$commandOptions = array_merge($commandOptions, $options);
		}

		$command = $this->client->getCommand('GetObject', $commandOptions);

		return $this->client->createPresignedRequest($command, "+".((int)$expiration)." minutes");
	}

	public function presignedUrl($key, $expiration = 0, $options = []) {
		$ignoreCDN = apply_filters('media-cloud/storage/ignore-cdn', false);

		if ((StorageToolSettings::driver() === 's3') && ($this->settings->validSignedCDNSettings()) && empty($ignoreCDN)) {
			if (empty($expiration)) {
				$expiration = $this->settings->presignedURLExpiration;
			}

			if (empty($expiration)) {
				$expiration = 1;
			}

			$cloudfrontClient = new CloudFrontClient([
				'profile' => 'default',
				'version' => 'latest',
				'region' => $this->settings->region
			]);

			$srcUrl = $this->client->getObjectUrl($this->settings->bucket, $key);
			$cdnHost = parse_url($this->settings->signedCDNURL, PHP_URL_HOST);
			$srcHost = parse_url($srcUrl, PHP_URL_HOST);
			$srcScheme = parse_url($srcUrl, PHP_URL_SCHEME);

			$srcUrl = str_replace($srcScheme, 'https', $srcUrl);
			$srcUrl = str_replace($srcHost, $cdnHost, $srcUrl);

			$url = $cloudfrontClient->getSignedUrl([
				'url' => $srcUrl,
				'expires' => time() + ($expiration * 60),
				'key_pair_id' => $this->settings->cloudfrontKeyID,
				'private_key' => $this->settings->cloudfrontPrivateKey
			]);

			return $url;
		} else {
		    $req = $this->presignedRequest($key, $expiration, $options);
		    $uri = $req->getUri();
		    $url = $uri->__toString();

		    return $url;
		}
	}

	public function url($key, $type = null) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		if (($type !== 'skip') && $this->usesSignedURLs($type)) {
			$expiration = $this->signedURLExpirationForType($type);
		    return $this->presignedUrl($key, $expiration);
        } else {
            return $this->client->getObjectUrl($this->settings->bucket, $key);
        }
	}
	//endregion

	//region Direct Uploads

	/**
	 * Returns the options data for generating the policy for uploads
	 *
	 * @param $acl
	 * @param $key
	 *
	 * @return array
	 */
	protected function getOptionsData($acl, $key) {
		$keyparts = explode('.', $key);

		return [
			['bucket' => $this->settings->bucket],
			['acl' => $acl],
//			['key' => $key],
			['starts-with', '$key', $keyparts[0]],
			['starts-with', '$Content-Type', '']
		];
	}

	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		try {
			$optionsData = $this->getOptionsData($acl, $key);

			if(!empty($cacheControl)) {
				$optionsData[] = ['Cache-Control' => $cacheControl];
			}

			if(!empty($expires)) {
				$optionsData[] = ['Expires' => $expires];
			}

			$postObject = new PostObjectV4($this->client, $this->settings->bucket, [], $optionsData, '+15 minutes');

			return new S3UploadInfo($key, $postObject, $acl, $cacheControl, $expires);
		}
		catch(AwsException $ex) {
			Logger::error('S3 Generate File Upload URL Error', ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

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
			$builder = new WizardBuilder('cloud-storage-s3', true);
		}

		$builder
			->section('cloud-storage-s3', true)
				->select('Getting Started', 'Learn about Amazon S3 and how to setup your Amazon AWS Account to work with Media Cloud.')
					->group('wizard.cloud-storage.providers.s3.intro', 'select-buttons')
						->option('watch-tutorial', 'Watch Video Tutorial', null, null, 'cloud-storage-s3-video-tutorial')
						->option('read-tutorial', 'Step By Step Tutorial', null, null, 'cloud-storage-s3-tutorial')
					->endGroup()
				->endStep()
				->form('wizard.cloud-storage.providers.s3.form', 'Amazon S3 Settings', 'Configure Media Cloud with your Amazon S3 settings.', [S3Storage::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-storage-settings'))
					->hiddenField('mcloud-storage-provider', 's3')
					->textField('mcloud-storage-s3-access-key', 'Access Key', '', null)
					->passwordField('mcloud-storage-s3-secret', 'Secret', '', null)
					->hiddenField('mcloud-storage-s3-region', 'auto')
					->textField('mcloud-storage-s3-bucket', 'Bucket', 'The name of bucket you wish to store your media in.', null)
					->checkboxField('mcloud-storage-s3-use-transfer-acceleration', 'Use Transfer Acceleration', 'Amazon S3 Transfer Acceleration enables fast, easy, and secure transfers of files over long distances between your client and an S3 bucket. Transfer Acceleration takes advantage of Amazon CloudFront’s globally distributed edge locations.  <strong>You must have it enabled on your bucket in the S3 console.</strong>', false)
				->endStep()
				->testStep('wizard.cloud-storage.providers.s3.test', 'Test Settings', 'Perform tests to insure that Amazon S3 is configured correctly.', false);

		static::addTests($builder);

		$builder->select('Complete', 'Basic setup is now complete!  Configure advanced settings or setup imgix.')
			->group('wizard.cloud-storage.providers.s3.success', 'select-buttons')
				->option('configure-imgix', 'Set Up imgix', null, null, 'imgix')
				->option('advanced-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings&tab=storage')
			->endGroup()
		->endStep();

//		$builder->next('wizard.cloud-storage.providers.s3.success', 'Complete', 'Success!  Everything is setup and ready to go.', null);

		$builder->section('cloud-storage-s3-video-tutorial', false)
				->video('https://www.youtube.com/watch?v=kjFCACrPRtU', null, null, null, true)
			->endSection();

		$builder
			->tutorialSection('cloud-storage-s3-tutorial', true)
				->tutorial('wizard.cloud-storage.providers.s3.tutorial.step-1', 'Create an S3 Bucket', 'Learn how to create an S3 bucket for use with Media Cloud.')
				->tutorial('wizard.cloud-storage.providers.s3.tutorial.step-2', 'Create Policy', 'Policies control how Media Cloud interacts with your S3 bucket and what it can do.', null, true)
				->tutorial('wizard.cloud-storage.providers.s3.tutorial.step-3', 'Create IAM User', 'Creates the IAM user credentials that Media Cloud will use to access your S3 bucket.', null, true)
				->tutorial('wizard.cloud-storage.providers.s3.tutorial.step-4', 'CORS Configuration (Optional)', 'If you plan on using the Direct Uploads functionality, learn how to configure CORS to make that happen.', null, true)
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
			'regionName' => 'mcloud-storage-s3-region',
			'endpointName' => 'mcloud-storage-s3-endpoint',
			'pathStyleEndpointName' => 'mcloud-storage-s3-use-path-style-endpoint',
			'useTransferAccelerationName' => 'mcloud-storage-s3-use-transfer-acceleration'
		];
	}

	public static function processWizardSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-storage-settings')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		/**
		 * @var string $providerName
		 * @var string $accessKeyName
		 * @var string $secretName
		 * @var string $bucketName
		 * @var string $regionName
		 * @var string $endpointName
		 * @var string $pathStyleEndpointName
		 * @var string $useTransferAccelerationName
		 */
		extract(static::fetchWizardInputNames(), EXTR_OVERWRITE);

		$provider = arrayPath($_POST, $providerName, null);
		$accessKey = arrayPath($_POST, $accessKeyName, null);
		$secret = arrayPath($_POST, $secretName, null);
		$bucket = arrayPath($_POST, $bucketName, null);
		$region = arrayPath($_POST, $regionName, 'auto');
		$endpoint = arrayPath($_POST, $endpointName, null);
		$pathStyleEndpoint = arrayPath($_POST, $pathStyleEndpointName, true);
		$useTransferAcceleration = arrayPath($_POST, $useTransferAccelerationName, false);

		if (!static::validateWizardInput($provider, $accessKey, $secret, $bucket, $region, $endpoint)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing required fields'], 200);
		}

		$oldProvider = Environment::ReplaceOption($providerName, $provider);
		$oldBucket = Environment::ReplaceOption($bucketName, $bucket);
		$oldKey = Environment::ReplaceOption($accessKeyName, $accessKey);
		$oldSecret = Environment::ReplaceOption($secretName, $secret);
		$oldRegion = Environment::ReplaceOption($regionName, $region);
		$oldEndpoint = Environment::ReplaceOption($endpointName, $endpoint);
		$oldPathStyleEndpoint = Environment::ReplaceOption($pathStyleEndpointName, $pathStyleEndpoint);
		$oldUseTransferAcceleration = Environment::ReplaceOption($useTransferAccelerationName, $useTransferAcceleration);

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
			Environment::UpdateOption($regionName, $oldRegion);
			Environment::UpdateOption($endpointName, $oldEndpoint);
			Environment::UpdateOption($pathStyleEndpointName, $oldPathStyleEndpoint);
			Environment::UpdateOption($useTransferAccelerationName, $oldUseTransferAcceleration);

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
			'key' => $this->settings->key,
			'secret' => $this->settings->secret,
			'bucket' => $this->bucket(),
			'region' => $this->region()
		];
	}
	//endregion
}
