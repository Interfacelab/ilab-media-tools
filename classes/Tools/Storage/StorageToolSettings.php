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

namespace MediaCloud\Plugin\Tools\Storage;

use MediaCloud\Plugin\Tools\ToolSettings;
use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Prefixer;
use function MediaCloud\Plugin\Utilities\vomit;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class StorageToolSettings
 * @package MediaCloud\Plugin\Tools\Storage
 *
 * @property string|null $prefixFormat
 * @property string[] $subsitePrefixFormat
 * @property string|null $cacheControl
 * @property-read string $privacy
 * @property-read string $privacyImages
 * @property-read string $privacyVideo
 * @property-read string $privacyAudio
 * @property-read string $privacyDocs
 * @property bool $uploadImages
 * @property bool $uploadAudio
 * @property bool $uploadVideo
 * @property bool $uploadDocuments
 * @property bool $deleteOnUpload
 * @property-read bool $queuedDeletes
 * @property int $queuedDeletesDelay
 * @property bool $deleteFromStorage
 * @property int $expireMinutes
 * @property-read string|null $expires
 * @property-read string[] $ignoredMimeTypes
 * @property-read string|null $cdn
 * @property-read string|null $docCdn
 * @property-read string|null $signedCDN
 * @property string|null $driver
 * @property bool $displayBadges
 * @property bool $mediaListIntegration
 * @property bool $enableBigImageSize
 * @property bool $uploadOriginal
 * @property bool $overwriteExisting
 * @property bool $cacheLookups
 * @property bool $skipOtherImport
 * @property bool $keepSubsitePath
 * @property bool $replaceAllImageUrls
 * @property bool $replaceAnchorHrefs
 * @property bool $filterContent
 * @property bool $useToolMenu
 * @property bool $useCompatibilityManager
 * @property bool $replaceSrcSet
 * @property bool $disableSrcSet
 * @property bool $disableEWWWBackgroundProcessing
 * @property bool $extractPDFPageSize
 *
 */
class StorageToolSettings extends ToolSettings {
	//region Static Class Variables

	/** @var string[] */
	private static $alternativeFormatTypes = ['image/pdf', 'application/pdf', 'image/psd', 'application/vnd.adobe.illustrator'];

	/** @var array  */
	private static $registry = [];

	/** @var StorageInterface|null  */
	private static $storageInterface = null;

	//endregion

	//region Class variables
	protected $settingsMap = [
		"deleteOnUpload" => ['mcloud-storage-delete-uploads', null, false],
		"deleteFromStorage" => ['mcloud-storage-delete-from-server', null, false],
		"prefixFormat" => ['mcloud-storage-prefix', null, ''],
		"subsitePrefixFormat" => ['mcloud-storage-subsite-prefixes', '', []],
		"uploadImages" => ['mcloud-storage-upload-images', null, true],
		"uploadAudio" => ['mcloud-storage-upload-audio', null, true],
		"uploadVideo" => ['mcloud-storage-upload-videos', null, true],
		"uploadDocuments" => ['mcloud-storage-upload-documents', null, true],
		"cacheControl" => ['mcloud-storage-cache-control', 'ILAB_AWS_S3_CACHE_CONTROL', null],
		"expireMinutes" => ['mcloud-storage-expires', 'ILAB_AWS_S3_EXPIRES', null],
		"driver" => ['mcloud-storage-provider','ILAB_CLOUD_STORAGE_PROVIDER', 's3'],
		"displayBadges" => ['mcloud-storage-display-badge', null, true],
		"mediaListIntegration" => ['mcloud-storage-display-media-list', null, true],
		"enableBigImageSize" => ['mcloud-storage-enable-big-size-threshold', null, true],
		"uploadOriginal" => ['mcloud-storage-big-size-upload-original', null, true],
		"overwriteExisting" => ["mcloud-storage-overwrite-existing", null, false],
		"cacheLookups" => ["mcloud-storage-cache-lookups", null, true],
		"skipOtherImport" => ["mcloud-storage-skip-import-other-plugin", null, false],
		"keepSubsitePath" => ["mcloud-storage-keep-subsite-path", null, false],
		"replaceAllImageUrls" => ["mcloud-storage-replace-all-image-urls", null, true],
		"replaceAnchorHrefs" => ["mcloud-storage-replace-hrefs", null, true],
		"filterContent" => ["mcloud-storage-filter-content", null, true],
		"useToolMenu" => ["mcloud-storage-display-tool-menu", null, true],
		"useCompatibilityManager" => ["mcloud-storage-enable-compatibility-manager", null, false],
		"disableSrcSet" => ["mcloud-storage-disable-srcset", null, false],
		"disableEWWWBackgroundProcessing" => ["mcloud-storage-disable-eww-background-processing", null, true],
		'queuedDeletesDelay' => ["mcloud-storage-queue-deletes-delay", null, 2],
		'extractPDFPageSize' => ["mcloud-storage-extract-pdf-page-size", null, false],
	];


	/** @var string|null */
	private $_expires = null;

	/** @var string */
	private $_privacy = null;

	/** @var string */
	private $_privacyImages = null;

	/** @var string */
	private $_privacyVideo = null;

	/** @var string */
	private $_privacyAudio = null;

	/** @var string */
	private $_privacyDocs = null;

	/** @var string[] */
	private $_ignoredMimeTypes = null;

	/** @var string|null */
	private $_docCdn = null;

	/** @var string|null */
	private $_cdn = null;

	/** @var string|null */
	private $_signedCDN = null;

	/** @var bool  */
	private $_queuedDeletes = null;

	/** @var null|array */
	private $allowedMimes = null;

	/** @var bool  */
	private $_replaceSrcSet = null;

	//endregion

	//region Constructor
	public function  __construct() {
		if (is_multisite() && is_network_admin() && empty($this->prefixFormat)) {
			$rootSiteName = get_network()->site_name;
			$adminUrl = network_admin_url('admin.php?page=media-cloud-settings&tab=storage#upload-handling');

			NoticeManager::instance()->displayAdminNotice('warning', "You are using Multisite WordPress but have not set a custom upload directory.  Your root site, <strong>'{$rootSiteName}'</strong> will be uploading to the root of your cloud storage which may not be desirable.  It's recommended that you set the Upload Directory to <code>sites/@{site-id}/@{date:Y/m}</code> in <a href='{$adminUrl}'>Cloud Storage</a> settings.", true, 'mcloud-multisite-missing-upload-path');
		}
	}
	//endregion

	//region Magic Methods
	public function __get($name) {
		if ($name === 'expires') {
			if (($this->_expires === null) && !empty($this->expireMinutes)) {
				$this->_expires = gmdate('D, d M Y H:i:s \G\M\T', time() + ($this->expireMinutes * 60));
			}

			return $this->_expires;
		}

		if ($name === 'privacy') {
			if ($this->_privacy === null) {
				if (StorageToolSettings::driver() === 'backblaze-s3') {
					$this->_privacy = Environment::Option('mcloud-storage-backblaze-s3-privacy', null, "public-read");
				} else {
					$this->_privacy = Environment::Option('mcloud-storage-privacy', null, "public-read");
				}
			}

			if(!in_array($this->_privacy, ['public-read', 'private', 'authenticated-read'])) {
				NoticeManager::instance()->displayAdminNotice('error', "Your AWS S3 settings are incorrect.  The ACL '{$this->privacy}' is not valid.  Defaulting to 'public-read'.");
				$this->_privacy = 'public-read';
			}

			return $this->_privacy;
		}

		if (in_array($name, ['privacyImages', 'privacyAudio', 'privacyVideo', 'privacyDocs'])) {
			if (StorageToolSettings::driver() === 'backblaze-s3') {
				return $this->privacy;
			}

			if ($name == "privacyImages") {
				if ($this->_privacyImages !== null) {
					return $this->_privacyImages;
				}

				$this->_privacyImages = Environment::Option('mcloud-storage-privacy-images', null, "inherit");
				if (empty($this->_privacyImages)) {
					$this->_privacyImages = 'inherit';
				}
				return $this->_privacyImages;
			}

			if ($name == "privacyAudio") {
				if ($this->_privacyAudio !== null) {
					return $this->_privacyAudio;
				}

				$this->_privacyAudio = Environment::Option('mcloud-storage-privacy-audio', null, "inherit");
				if (empty($this->_privacyAudio)) {
					$this->_privacyAudio = 'inherit';
				}

				return $this->_privacyAudio;
			}

			if ($name == "privacyVideo") {
				if ($this->_privacyVideo !== null) {
					return $this->_privacyVideo;
				}

				$this->_privacyVideo = Environment::Option('mcloud-storage-privacy-video', null, "inherit");
				if (empty($this->_privacyVideo)) {
					$this->_privacyVideo = 'inherit';
				}

				return $this->_privacyVideo;
			}

			if ($name == "privacyDocs") {
				if ($this->_privacyDocs !== null) {
					return $this->_privacyDocs;
				}

				$this->_privacyDocs = Environment::Option('mcloud-storage-privacy-docs', null, "inherit");
				if (empty($this->_privacyDocs)) {
					$this->_privacyDocs = 'inherit';
				}

				return $this->_privacyDocs;
			}
		}

		if ($name === 'ignoredMimeTypes') {
			if ($this->_ignoredMimeTypes === null) {
				$this->_ignoredMimeTypes = [];

				$ignored = Environment::Option('mcloud-storage-ignored-mime-types', null, '');
				$ignored_lines = explode("\n", $ignored);
				if(count($ignored_lines) <= 1) {
					$ignored_lines = explode(',', $ignored);
				}

				foreach($ignored_lines as $d) {
					if(!empty($d)) {
						$this->_ignoredMimeTypes[] = trim($d);
					}
				}

				if (empty($this->uploadImages)) {
					$this->_ignoredMimeTypes[] = 'image/*';
				}

				if (empty($this->uploadVideo)) {
					$this->_ignoredMimeTypes[] = 'video/*';
				}

				if (empty($this->uploadAudio)) {
					$this->_ignoredMimeTypes[] = 'audio/*';
				}

				if (empty($this->uploadDocuments)) {
					$this->_ignoredMimeTypes[] = 'application/*';
					$this->_ignoredMimeTypes[] = 'text/*';
				}
			}

			return $this->_ignoredMimeTypes;
		}

		if ($name === 'cdn') {
			if ($this->_cdn === null) {
				$this->_cdn = rtrim(Environment::Option('mcloud-storage-cdn-base', 'ILAB_AWS_S3_CDN_BASE'),'/');
				if (!empty($this->_cdn) && empty(parse_url($this->_cdn, PHP_URL_HOST))) {
					$this->_cdn = 'https://'.$this->_cdn;
				}
			}

			return $this->_cdn;
		}

		if ($name === 'docCdn') {
			if ($this->_docCdn === null) {
				$this->_docCdn = rtrim(Environment::Option('mcloud-storage-doc-cdn-base', 'ILAB_AWS_S3_DOC_CDN_BASE', $this->cdn), '/');
				if (!empty($this->_docCdn) && empty(parse_url($this->_docCdn, PHP_URL_HOST))) {
					$this->_docCdn = 'https://'.$this->_docCdn;
				}
			}

			return $this->_docCdn;
		}

		if ($name === 'signedCDN') {
			if ($this->_signedCDN === null) {
				$this->_signedCDN = rtrim(Environment::Option('mcloud-storage-signed-cdn-base', null, null), '/');
				if (!empty($this->_signedCDN) && empty(parse_url($this->_signedCDN, PHP_URL_HOST))) {
					$this->_signedCDN = 'https://'.$this->_signedCDN;
				}
			}

			return $this->_signedCDN;
		}

		if ($name === 'queuedDeletes') {
			$canQueue = apply_filters('media-cloud/storage/queue-deletes', true);

			if ($this->_queuedDeletes === null) {
				$this->_queuedDeletes = Environment::Option('mcloud-storage-queue-deletes', null, false);
			}

			return ($canQueue && !empty($this->_queuedDeletes));
		}

		if ($name === 'replaceSrcSet') {
			if ($this->_replaceSrcSet !== null) {
				return $this->_replaceSrcSet;
			}

			global $wp_version;
			$this->_replaceSrcSet = empty($this->disableSrcSet) && version_compare($wp_version, '5.3', '>=');
			if (!empty($this->_replaceSrcSet)) {
				$this->_replaceSrcSet = Environment::Option('mcloud-storage-replace-srcset', null, false);
			}

			return $this->_replaceSrcSet;
		}


		return parent::__get($name);
	}

	public function __isset($name) {
		if (in_array($name, ['disableSrcSet', 'replaceSrcSet', 'expires', 'privacy', 'ignoredMimeTypes', 'cdn', 'docCdn', 'signedCDN', 'privacyImages', 'privacyAudio', 'privacyVideo', 'privacyDocs'])) {
			return true;
		}

		return parent::__isset($name);
	}

	//endregion

	//region Settings Properties
	/** @return string|null */
	public static function prefixFormat() {
		if (is_multisite()) {
			$blogId = get_current_blog_id();
			if (isset(static::instance()->subsitePrefixFormat[$blogId])) {
				$prefix = static::instance()->subsitePrefixFormat[$blogId];
				if (!empty($prefix)) {
					return $prefix;
				}
			}


		}

		return static::instance()->prefixFormat;
	}

	/**
	 * @param int|null $id
	 * @return string
	 */
	public static function prefix($id = null) {
		return Prefixer::Parse(static::prefixFormat(), $id);
	}

	/** @return string|null */
	public static function cacheControl() {
		return static::instance()->cacheControl;
	}

	/** @return string|null */
	public static function expires() {
		return static::instance()->expires;
	}

	/**
	 * @param false|null|string $type
	 *
	 * @return string
	 */
	public static function privacy($type = null) {
		/** @var StorageToolSettings $instance */
		$instance = static::instance();

		if (empty($type)) {
			return $instance->privacy;
		}

		if (strpos($type, 'image') === 0) {
			return ($instance->privacyImages === 'inherit') ? $instance->privacy : $instance->privacyImages;
		}

		if (strpos($type, 'video') === 0) {
			return ($instance->privacyVideo === 'inherit') ? $instance->privacy : $instance->privacyVideo;
		}

		if (strpos($type, 'audio') === 0) {
			return ($instance->privacyAudio === 'inherit') ? $instance->privacy : $instance->privacyAudio;
		}

		if ((strpos($type, 'application') === 0) || (strpos($type, 'text') === 0)) {
			return ($instance->privacyDocs === 'inherit') ? $instance->privacy : $instance->privacyDocs;
		}

		return $instance->privacy;
	}

	/**
	 * @return array|null
	 */
	public static function ignoredMimeTypes() {
		return static::instance()->ignoredMimeTypes;
	}

	/**
	 * @return bool|null
	 *
	 * @param string[] $additionalTypes
	 */
	public static function mimeTypeIsIgnored($mimeType, $additionalTypes = []) {
		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);

		$ignored = array_merge(static::instance()->ignoredMimeTypes, $additionalTypes);

		foreach($ignored as $mimeTypeTest) {
			if ($mimeType == $mimeTypeTest) {
				return true;
			}

			if (strpos($mimeTypeTest, '*') !== false) {
				$mimeTypeRegex = str_replace('*', '[aA-zZ0-9_.-]+', $mimeTypeTest);
				$mimeTypeRegex = str_replace('/','\/', $mimeTypeRegex);

				if (preg_match("/{$mimeTypeRegex}/m", $mimeType)) {
					if (static::uploadImages() && $altFormatsEnabled && (in_array($mimeType, static::$alternativeFormatTypes))) {
						return false;
					}

					return true;
				}
			}
		}

		return false;
	}

	public static function allowedMimeTypes() {
		if (static::instance()->allowedMimes != null) {
			return static::instance()->allowedMimes;
		}

		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);
		$allowed = get_allowed_mime_types();

		if (static::uploadImages() && $altFormatsEnabled) {
			$allowed = array_merge($allowed, static::$alternativeFormatTypes);
		}

		$result = [];
		foreach($allowed as $mime) {
			if (static::mimeTypeIsIgnored($mime)) {
				continue;
			}

			$result[] = $mime;
		}

		static::instance()->allowedMimes = $result;

		return $result;
	}

	/**
	 * @return bool|null
	 */
	public static function uploadDocuments() {
		return static::instance()->uploadDocuments;
	}

	/**
	 * @return bool|null
	 */
	public static function uploadImages() {
		return static::instance()->uploadImages;
	}

	/**
	 * @return bool|null
	 */
	public static function uploadAudio() {
		return static::instance()->uploadAudio;
	}

	/**
	 * @return bool|null
	 */
	public static function uploadVideo() {
		return static::instance()->uploadVideo;
	}

	/** @return string|null */
	public static function docCdn() {
		return static::instance()->docCdn;
	}

	/** @return string|null */
	public static function cdn() {
		return static::instance()->cdn;
	}

	/** @return string|null */
	public static function signedCDN() {
		return static::instance()->signedCDN;
	}

	/**
	 * @return bool|null
	 */
	public static function deleteOnUpload() {
		return static::instance()->deleteOnUpload;
	}

	/**
	 * @return bool|null
	 */
	public static function queuedDeletes() {
		return static::instance()->queuedDeletes;
	}

	/**
	 * @return int
	 */
	public static function queuedDeletesDelay() {
		return static::instance()->queuedDeletesDelay;
	}

	/**
	 * @return bool|null
	 */
	public static function deleteFromStorage() {
		return static::instance()->deleteFromStorage;
	}

	/**
	 * @return array|null
	 */
	public static function alternativeFormatTypes() {
		return static::$alternativeFormatTypes;
	}
	//endregion

	//region Storage Manager
	/**
	 * Gets the currently configured storage interface.
	 *
	 * @return StorageInterface
	 */
	public static function storageInstance() {
		if (static::$storageInterface) {
			return static::$storageInterface;
		}

		if (!isset(static::$registry[static::driver()])) {
			return null;
		}

		$class = static::$registry[static::driver()]['class'];
		static::$storageInterface = new $class();

		return static::$storageInterface;
	}

	public static function driver() {
		return static::instance()->driver;
	}

	/**
	 * Resets the current storage interface
	 */
	public static function resetStorageInstance() {
		static::instance()->resetProperty('driver');
		static::$storageInterface = null;
	}

	/**
	 * Registers a storage driver
	 * @param $identifier
	 * @param $name
	 * @param $class
	 * @param $config
	 */
	public static function registerDriver($identifier, $name, $class, $config, $help) {
		static::$registry[$identifier] = [
			'name' => $name,
			'class' => $class,
			'config' => $config,
			'help' => $help
		];
	}

	/**
	 * @param $identifier
	 *
	 * @return string
	 */
	public static function driverClass($identifier) {
		if (!isset(static::$registry[$identifier])) {
			return null;
		}

		return static::$registry[$identifier]['class'];
	}

	/**
	 * Current driver name
	 * @return string
	 */
	public static function currentDriverName() {
		$driver = static::driver();

		if (!isset(self::$registry[$driver])) {
			return null;
		}

		$class = static::driverClass($driver);
		if (empty($class)) {
			return null;
		}

		return $class::name();
	}

	/**
	 * @param $identifier
	 *
	 * @return string
	 */
	public static function driverName($identifier) {
		if (!isset(static::$registry[$identifier])) {
			return null;
		}

		return static::$registry[$identifier]['name'];
	}

	/**
	 * @param $identifier
	 *
	 * @return array
	 */
	public static function driverConfig($identifier) {
		if (!isset(static::$registry[$identifier])) {
			return null;
		}

		return static::$registry[$identifier]['config'];
	}

	/**
	 * @return array
	 */
	public static function drivers() {
		return static::$registry;
	}
	//endregion
}