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

namespace ILAB\MediaCloud\Cloud\Storage\Driver;

use ILAB\MediaCloud\Cloud\Storage\InvalidStorageSettingsException;
use ILAB\MediaCloud\Cloud\Storage\StorageException;
use ILAB\MediaCloud\Cloud\Storage\StorageInterface;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use ILAB\MediaCloud\Utilities\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB_Aws\Exception\AwsException;
use ILAB_Aws\S3\S3Client;
use ILAB_Aws\S3\S3MultiRegionClient;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class S3 implements StorageInterface {
	//region Properties
	/*** @var string */
	private $key = null;

	/*** @var string */
	private $secret = null;

	/*** @var string */
	private $bucket = null;

	/*** @var string */
	private $region = false;

	/*** @var bool */
	private $settingsError = false;

	/*** @var string */
	private $endpoint = null;

	/*** @var bool */
	private $endPointPathStyle = true;

	/*** @var bool */
	private $useTransferAcceleration = false;

	/*** @var S3Client|S3MultiRegionClient|null */
	private $client = null;
	//endregion

	//region Constructor
	public function __construct() {
		$this->bucket = EnvironmentOptions::Option('ilab-media-s3-bucket', [
			'ILAB_AWS_S3_BUCKET',
			'ILAB_CLOUD_BUCKET'
		]);

		$this->key    = EnvironmentOptions::Option('ilab-media-s3-access-key', [
			'ILAB_AWS_S3_ACCESS_KEY',
			'ILAB_CLOUD_ACCESS_KEY'
		]);

		$this->secret = EnvironmentOptions::Option('ilab-media-s3-secret', [
			'ILAB_AWS_S3_ACCESS_SECRET',
			'ILAB_CLOUD_ACCESS_SECRET'
		]);

		$this->useTransferAcceleration = EnvironmentOptions::Option('ilab-media-s3-use-transfer-acceleration', 'ILAB_AWS_S3_TRANSFER_ACCELERATION', false);

		$this->endpoint = EnvironmentOptions::Option('ilab-media-s3-endpoint', [
			'ILAB_AWS_S3_ENDPOINT',
			'ILAB_CLOUD_ENDPOINT'
		], false);

		$this->endPointPathStyle = EnvironmentOptions::Option('ilab-media-s3-use-path-style-endpoint', [
			'ILAB_AWS_S3_ENDPOINT_PATH_STYLE',
			'ILAB_CLOUD_ENDPOINT_PATH_STYLE'
		], true);

		$this->settingsError = get_option('ilab-cloud-settings-error', false);

		$region = EnvironmentOptions::Option('ilab-media-s3-region', [
			'ILAB_AWS_S3_REGION',
			'ILAB_CLOUD_REGION'
		], 'auto');

		if($region != 'auto') {
			$this->region = $region;
		}

		$this->client = $this->getClient();
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	public function validateSettings() {
		delete_option('ilab-s3-settings-error');
		$this->settingsError = false;

		$this->client = null;
		if($this->enabled()) {
			$client = $this->getClient();

			$valid = false;
			if($client) {
				if($client->doesBucketExist($this->bucket)) {
					$valid = true;
				} else {
					try {
						Logger::info("Bucket does not exist, trying to list buckets.");

						$result  = $client->listBuckets();
						$buckets = $result->get('Buckets');
						if( ! empty($buckets)) {
							foreach($buckets as $bucket) {
								if($bucket['Name'] == $this->bucket) {
									$valid = true;
									break;
								}
							}
						}

						Logger::info("Bucket does not exist.");
					}
					catch(AwsException $ex) {
						Logger::error("Error insuring bucket exists.", [ 'exception' => $ex->getMessage() ]);
					}
				}
			}

			if(!$valid) {
				$this->settingsError = true;
				update_option('ilab-s3-settings-error', true);
			} else {
				$this->client = $client;
			}
		}
	}

	public function enabled() {
		if (!($this->key && $this->secret && $this->bucket)) {
			NoticeManager::instance()->displayAdminNotice('error',"To start using Cloud Storage, you will need to <a href='admin.php?page={$this->options_page}'>supply your AWS credentials.</a>.");
			return false;
		}

		if ($this->settingsError) {
			return false;
		}

		return true;
	}
	//endregion

	//region Client Creation

	/**
	 * Attempts to determine the region for the bucket
	 * @return bool|string
	 */
	protected function getBucketRegion() {
		if (!$this->enabled()) {
			return false;
		}

		$config = [
			'version' => 'latest',
			'credentials' => [
				'key'    => $this->key,
				'secret' => $this->secret
			]
		];

		if (!empty($this->endpoint)) {
			$config['endpoint'] = $this->endpoint;
			if ($this->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		$s3=new S3MultiRegionClient($config);
		$region = false;
		try {
			$region = $s3->determineBucketRegion($this->bucket);
		} catch (AwsException $ex) {
			Logger::error( "AWS Error fetching region", [ 'exception' => $ex->getMessage()]);
		}

		return $region;
	}

	/**
	 * Builds and returns an S3MultiRegionClient
	 * @return S3MultiRegionClient|null
	 */
	protected function getS3MultiRegionClient() {
		if (!$this->enabled()) {
			return null;
		}

		$config = [
			'version' => 'latest',
			'credentials' => [
				'key'    => $this->key,
				'secret' => $this->secret
			]
		];

		if (!empty($this->endpoint)) {
			$config['endpoint'] = $this->endpoint;

			if ($this->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		if ($this->useTransferAcceleration) {
			$config['use_accelerate_endpoint'] = true;
		}

		$s3=new S3MultiRegionClient($config);
		return $s3;
	}

	/**
	 * Attempts to build the S3Client.  This requires a region be defined or determinable.
	 * @param bool $region
	 * @return S3Client|null
	 */
	protected function getS3Client($region = false) {
		if (!$this->enabled()) {
			return null;
		}

		if (empty($region)) {
			if (empty($this->region)) {
				$this->region = $this->getBucketRegion();
				if (empty($this->region)) {
					Logger::info( "Could not get region from server.");
					return null;
				}

				update_option('ilab-media-s3-region', $this->region);
			}

			$region = $this->region;
		}

		if (empty($region)) {
			return null;
		}

		$config = [
			'version' => 'latest',
			'credentials' => [
				'key'    => $this->key,
				'secret' => $this->secret
			],
			'region' => $region
		];

		if (!empty($this->endpoint)) {
			$config['endpoint'] = $this->endpoint;
			if ($this->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		if ($this->useTransferAcceleration) {
			$config['use_accelerate_endpoint'] = true;
		}

		$s3=new S3Client($config);
		return $s3;
	}


	protected function getClient() {
		if (!$this->enabled()) {
			return null;
		}

		$s3 = $this->getS3Client();
		if (!$s3) {
			Logger::info( 'Could not create regular client, creating multi-region client instead.');
			$s3 = $this->getS3MultiRegionClient();
		}

		return $s3;
	}
	//endregion

	//region File Functions
	public function exists( $key ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->doesObjectExist($this->bucket, $key);
	}

	public function copy( $sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$copyOptions = [
			'MetadataDirective' => 'REPLACE',
			'Bucket' => $this->bucket,
			'Key' => $destKey,
			'CopySource' => $this->bucket.'/'.$sourceKey,
			'ACL' => $acl
		];

		if ($cacheControl) {
			$copyOptions['CacheControl'] = $cacheControl;
		}

		if ($expires) {
			$copyOptions['Expires'] = $expires;
		}

		if ($mime) {
			$copyOptions['ContentType'] = $mime;
		}

		try {
			$this->client->copyObject($copyOptions);
		}
		catch (AwsException $ex) {
			Logger::error( 'S3 Error Copying Object', [ 'exception' =>$ex->getMessage(), 'options' =>$copyOptions]);
			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function upload( $key, $file, $acl, $cacheControl = false, $expires = false ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$params = [];
		$options = [];

		if ($cacheControl) {
			$params['CacheControl'] = $cacheControl;
		}

		if ($expires) {
			$params['Expires'] = $expires;
		}

		if (!empty($params)) {
			$options['params'] = $params;
		}

		try {
			Logger::startTiming( "Start Upload", [ 'file' => $key]);
			$result = $this->client->upload($this->bucket,$key,$file, $acl, $options);
			Logger::endTiming( "End Upload", [ 'file' => $key]);

			return $result->get('ObjectURL');
		} catch (AwsException $ex) {
			Logger::error( 'S3 Upload Error', [
				'exception' => $ex->getMessage(),
			    'bucket' => $this->bucket,
				'key' => $key,
				'privacy' =>$acl,
				'options' =>$options
			]);

			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function delete( $key ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$this->client->deleteObject(['Bucket' => $this->bucket, 'Key' => $this->key]);
		} catch (AwsException $ex) {
			Logger::error( 'S3 Delete File Error', [ 'exception' =>$ex->getMessage(), 'Bucket' =>$this->bucket, 'Key' =>$key]);
		}
	}

	public function info( $key ) {
		// TODO: Implement info() method.
	}
	//endregion

	//region URLs
	public function presignedUrl( $key ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$command = $this->client->getCommand('GetObject', ['Bucket' => $this->bucket, 'Key' => $key]);
		$presignedRequest = $this->client->createPresignedRequest($command, '+10 minutes');
		return (string) $presignedRequest->getUri();
	}

	public function url( $key ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		return $this->client->getObjectUrl($this->bucket, $key);
	}
	//endregion

	//region Direct Uploads
	public function renderDirectUploadScripts() {
		// TODO: Implement renderDirectUploadScripts() method.
	}
	//endregion
}
