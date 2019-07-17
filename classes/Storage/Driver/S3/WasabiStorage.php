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

namespace ILAB\MediaCloud\Storage\Driver\S3;

use FasterImage\FasterImage;
use ILAB\MediaCloud\Storage\FileInfo;
use ILAB\MediaCloud\Storage\InvalidStorageSettingsException;
use ILAB\MediaCloud\Storage\StorageException;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILABAmazon\Exception\AwsException;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class WasabiStorage extends OtherS3Storage {
	//region Properties

	//endregion

	//region Constructor
	public function __construct() {
		parent::__construct();

		$region = Environment::Option('mcloud-storage-wasabi-region', null, null);
		if(!empty($region)) {
			$this->region = $region;
		}
	}
	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'wasabi';
	}

	public static function name() {
		return 'Wasabi';
	}

	public static function bucketLink($bucket) {
		return "https://console.wasabisys.com/#/file_manager/$bucket";
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		return "https://console.wasabisys.com/#/file_manager/$bucket/$key";
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	protected function settingsErrorOptionName() {
		return 'ilab-wasabi-settings-error';
	}

	public static function endpoint() {
		$region = Environment::Option('mcloud-storage-wasabi-region', null, null);
		if(!empty($region)) {
			return "https://s3.{$region}.wasabisys.com";
		}

		return "https://s3.wasabisys.com";
	}

	public static function pathStyleEndpoint() {
		return true;
	}

	public static function defaultRegion() {
		return null;
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions

	public function insureACL($key, $acl) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$this->client->putObjectAcl(['Bucket' => $this->bucket, 'Key' => $key, 'ACL' => $acl]);
		} catch (AwsException $ex) {
			throw new StorageException($ex->getMessage(), $ex->getStatusCode(), $ex);
		}
	}

	public function info($key) {
		if(!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		try {
			$result = $this->client->headObject(['Bucket' => $this->bucket, 'Key' => $key]);
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
			$result = $faster->batch([$presignedUrl]);
			$result = $result[$presignedUrl];
			$size = $result['size'];
		}

		$fileInfo = new FileInfo($key, $url, $presignedUrl, $length, $type, $size);

		return $fileInfo;
	}
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		try {
			$optionsData = [
				'ACL' => $acl,
				'Bucket' => $this->bucket,
				'ContentType' => $mimeType,
				'Key' => $key
			];

			if(!empty($cacheControl)) {
				$optionsData[] = ['CacheControl' => $cacheControl];
			}

			if(!empty($expires)) {
				$optionsData[] = ['Expires' => $expires];
			}

			$putCommand = $this->client->getCommand('PutObject',$optionsData);
			$request = $this->client->createPresignedRequest($putCommand, '+20 minutes');
			$signedUrl = (string)$request->getUri();

			return new OtherS3UploadInfo($key,$signedUrl,$acl);
		}
		catch(AwsException $ex) {
			Logger::error('S3 Generate File Upload URL Error', ['exception' => $ex->getMessage()]);
			throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-upload-other-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-other-s3.js', [], false, true);
	}
	//endregion
}
