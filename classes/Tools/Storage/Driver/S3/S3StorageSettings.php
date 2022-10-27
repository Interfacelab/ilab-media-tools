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

use MediaCloud\Plugin\Tools\Storage\StorageInterface;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\ToolSettings;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use function MediaCloud\Plugin\Utilities\gen_uuid;

/**
 * Class S3StorageSettings
 * @package MediaCloud\Plugin\Tools\Storage\Driver\S3
 *
 * @property string key
 * @property string secret
 * @property string bucket
 * @property bool useCredentialProvider
 * @property string region
 * @property string endpoint
 * @property bool endPointPathStyle
 * @property bool useTransferAcceleration
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
 * @property bool settingsError
 * @property string signedCDNURL
 * @property string cloudfrontKeyID
 * @property-read string cloudfrontPrivateKey
 *
 */
class S3StorageSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'key' => ['mcloud-storage-s3-access-key', ['ILAB_AWS_S3_ACCESS_KEY', 'ILAB_CLOUD_ACCESS_KEY'], null],
		'secret' => ['mcloud-storage-s3-secret', ['ILAB_AWS_S3_ACCESS_SECRET', 'ILAB_CLOUD_ACCESS_SECRET'], null],
		'bucket' => ['mcloud-storage-s3-bucket', ['ILAB_AWS_S3_BUCKET','ILAB_CLOUD_BUCKET'], null],
		'useCredentialProvider' => ['mcloud-storage-s3-use-credential-provider', ['ILAB_AWS_S3_USE_CREDENTIAL_PROVIDER', 'ILAB_CLOUD_USE_CREDENTIAL_PROVIDER'], false],
		'useTransferAcceleration' => ['mcloud-storage-s3-use-transfer-acceleration', 'ILAB_AWS_S3_TRANSFER_ACCELERATION', false],
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
		'settingsError' => ['ilab-backblaze-settings-error',  null, false],
		'signedCDNURL' => ['mcloud-storage-signed-cdn-base',  null, false],
		'cloudfrontKeyID' => ['mcloud-storage-cloudfront-key-id',  null, false],
	];

	/** @var S3StorageInterface  */
	private $storage = null;

	private $_endpoint = null;
	private $_pathStyleEndpoint = null;
	private $_region = null;
	private $_settingsError = null;
	private $_cloudfrontPrivateKey = null;

	private $deletePrivateKey = false;

	/** @var callable */
	private $dieHandler = null;

	/**
	 * S3StorageSettings constructor.
	 *
	 * @param StorageInterface $storage
	 */
	public function __construct($storage) {
		$this->storage = $storage;

		add_filter('wp_die_ajax_handler', [$this, 'hookDieHandler']);
		add_filter('wp_die_json_handler', [$this, 'hookDieHandler']);
		add_filter('wp_die_jsonp_handler', [$this, 'hookDieHandler']);
		add_filter('wp_die_xmlrpc_handler', [$this, 'hookDieHandler']);
		add_filter('wp_die_xml_handler', [$this, 'hookDieHandler']);
		add_filter('wp_die_handler', [$this, 'hookDieHandler']);
	}

	public function __get($name) {
		if ($name === 'endpoint') {
			if (StorageToolSettings::driver() === 's3') {
				return null;
			}

			if ($this->_endpoint !== null) {
				return $this->_endpoint;
			}

			/** @var StorageInterface $storageClass */
			$storageClass = get_class($this->storage);

			if ($storageClass::endpoint() !== null) {
				$this->_endpoint = $storageClass::endpoint();
			} else {
				$this->_endpoint = Environment::Option('mcloud-storage-s3-endpoint', ['ILAB_AWS_S3_ENDPOINT', 'ILAB_CLOUD_ENDPOINT'], null);
			}

			if(!empty($this->_endpoint)) {
				if(!preg_match('#^[aA-zZ0-9]+\:\/\/#', $this->_endpoint)) {
					$this->_endpoint = 'https://'.$this->_endpoint;
				}
			}

			return $this->_endpoint;
		} else if ($name === 'endPointPathStyle') {
			if ($this->_pathStyleEndpoint !== null) {
				return $this->_pathStyleEndpoint;
			}

			/** @var StorageInterface $storageClass */
			$storageClass = get_class($this->storage);

			if ($storageClass::pathStyleEndpoint() !== null) {
				$this->_pathStyleEndpoint = $storageClass::pathStyleEndpoint();
			} else {
				$this->_pathStyleEndpoint = Environment::Option('mcloud-storage-s3-use-path-style-endpoint', ['ILAB_AWS_S3_ENDPOINT_PATH_STYLE', 'ILAB_CLOUD_ENDPOINT_PATH_STYLE'], true);
			}

			return $this->_pathStyleEndpoint;

		} else if ($name === 'region') {

			if ($this->_region !== null) {
				return $this->_region;
			}

			/** @var StorageInterface $storageClass */
			$storageClass = get_class($this->storage);

			if ($storageClass::defaultRegion() !== null) {
				$this->region = $storageClass::defaultRegion();
			} else if ((StorageToolSettings::driver() !== 'wasabi') && (StorageToolSettings::driver() !== 'do')) {
				$region = Environment::Option('mcloud-storage-s3-region', ['ILAB_AWS_S3_REGION', 'ILAB_CLOUD_REGION'], 'auto');

				if ($region === 'custom') {
					$region = Environment::Option('mcloud-storage-s3-custom-region', null, 'auto');
				}

				if($region !== 'auto') {
					$this->_region = $region;
				}
			} else if (StorageToolSettings::driver() === 'wasabi') {
				$this->_region = Environment::Option('mcloud-storage-wasabi-region', null, null);
			}

			return $this->_region;
		} else if ($name === 'settingsError') {
			if ($this->_settingsError === null) {
				return $this->_settingsError;
			}

			/** @var S3StorageInterface $storageClass */
			$storageClass = get_class($this->storage);
			$this->_settingsError = Environment::Option($storageClass::settingsErrorOptionName(), null, false);
			return $this->_settingsError;
		} else if ($name === 'cloudfrontPrivateKey') {
			if ($this->_cloudfrontPrivateKey !== null) {
				return $this->_cloudfrontPrivateKey;
			}

			$keyTemp = tempnam('/tmp', gen_uuid(8));

			$keyFile = Environment::Option('mcloud-storage-cloudfront-private-key-file', null, null);
			if (!empty($keyFile) && file_exists($keyFile)) {
				$this->deletePrivateKey = false;
				$this->_cloudfrontPrivateKey = $keyFile;
			}

			if (empty($this->_cloudfrontPrivateKey)) {
				$keyContents = Environment::Option('mcloud-storage-cloudfront-private-key');
				if (!empty($keyContents)) {
					$this->deletePrivateKey = true;
					file_put_contents($keyTemp, $keyContents);
					$this->_cloudfrontPrivateKey = $keyTemp;
				}
			}

			return $this->_cloudfrontPrivateKey;
		}

		return parent::__get($name);
	}

	public function __set($name, $value) {
		if ($name === 'region') {
			$this->_region = $value;
			Environment::UpdateOption('mcloud-storage-s3-region', $value);
		} else if ($name === 'settingsError') {
			/** @var S3StorageInterface $storageClass */
			$storageClass = get_class($this->storage);
			$this->_settingsError = $value;
			Environment::UpdateOption($storageClass::settingsErrorOptionName(), $value);
		}

		parent::__set($name, $value);
	}

	public function __isset($name) {
		if (in_array($name, ['region', 'endPointPathStyle', 'endpoint', 'settingsError', 'cloudfrontPrivateKey'])) {
			return true;
		}

		return parent::__isset($name);
	}

	public function validSignedCDNSettings() {
		return (!empty($this->signedCDNURL) && !empty($this->cloudfrontKeyID) && !empty($this->cloudfrontPrivateKey));
	}

	public function hookDieHandler($handler) {
		$this->dieHandler = $handler;
		return [$this, 'deletePrivateKey'];
	}

	public function deletePrivateKey($message, $title = '', $args = array()) {
		if ($this->deletePrivateKey && file_exists($this->_cloudfrontPrivateKey)) {
			unlink($this->_cloudfrontPrivateKey);
		}

		call_user_func( $this->dieHandler, $message, $title, $args );
	}

}
