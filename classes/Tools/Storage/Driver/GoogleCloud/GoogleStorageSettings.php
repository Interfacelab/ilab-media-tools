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

use MediaCloud\Plugin\Tools\ToolSettings;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;

/**
 * Class S3StorageSettings
 * @package MediaCloud\Plugin\Tools\Storage\Driver\S3
 *
 * @property-read string credentials
 * @property string bucket
 * @property bool useBucketPolicyOnly
 * @property bool usePresignedURLs
 * @property bool usePresignedURLsForImages
 * @property bool usePresignedURLsForVideo
 * @property bool usePresignedURLsForAudio
 * @property bool usePresignedURLsForDocs
 * @property int presignedURLExpiration
 * @property int presignedURLExpirationForImages
 * @property int presignedURLExpirationForVideo
 * @property int presignedURLExpirationForAudio
 * @property int presignedURLExpirationForDocs
 *
 */
class GoogleStorageSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'bucket' => ['mcloud-storage-google-bucket', ['ILAB_CLOUD_GOOGLE_BUCKET', 'ILAB_AWS_S3_BUCKET', 'ILAB_CLOUD_BUCKET'], null],
		'useBucketPolicyOnly' => ['mcloud-storage-bucket-policy-only', null, false],
		'usePresignedURLs' => ['mcloud-storage-use-presigned-urls', null, false],
		'presignedURLExpiration' => ['mcloud-storage-presigned-expiration', null, 300],
		'usePresignedURLsForImages' => ['mcloud-storage-use-presigned-urls-images', null, false],
		'presignedURLExpirationForImages' => ['mcloud-storage-presigned-expiration-images', null, 0],
		'usePresignedURLsForAudio' => ['mcloud-storage-use-presigned-urls-audio', null, false],
		'presignedURLExpirationForAudio' => ['mcloud-storage-presigned-expiration-audio', null, 0],
		'usePresignedURLsForVideo' => ['mcloud-storage-use-presigned-urls-video', null, false],
		'presignedURLExpirationForVideo' => ['mcloud-storage-presigned-expiration-video', null, 0],
		'usePresignedURLsForDocs' => ['mcloud-storage-use-presigned-urls-docs', null, false],
		'presignedURLExpirationForDocs' => ['mcloud-storage-presigned-expiration-docs', null, 0],
	];

	private $_credentials = null;

	public function __get($name) {
		if ($name === 'credentials') {
			if ($this->_credentials !== null) {
				return $this->_credentials;
			}

			$credFile = Environment::Option('mcloud-storage-google-credentials-file', 'ILAB_CLOUD_GOOGLE_CREDENTIALS');
			if (!empty($credFile)) {
				if (file_exists($credFile)) {
					$this->_credentials = json_decode(file_get_contents($credFile), true);
				} else {
					Logger::error("Credentials file '$credFile' could not be found.", [], __METHOD__, __LINE__);
				}
			}

			if (empty($this->_credentials)) {
				$creds = Environment::Option('mcloud-storage-google-credentials');
				if (!empty($creds)) {
					$this->_credentials = json_decode($creds, true);
				}
			}

			return $this->_credentials;
		}

		return parent::__get($name);
	}

	public function __isset($name) {
		if (in_array($name, ['credentials'])) {
			return true;
		}

		return parent::__isset($name);
	}
}
