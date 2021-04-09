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

use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Prefixer;

if (!defined('ABSPATH')) { header('Location: /'); die; }

final class StorageGlobals {
	//region Class variables
	/** @var StorageGlobals */
	private static $instance = null;

	/** @var string|null */
	private $prefixFormat = '';

	/** @var string[] */
	private $subsitePrefixFormat = [];

	/** @var string|null */
	private $cacheControl = null;

	/** @var string|null */
	private $expires = null;

	/** @var string */
	private $privacy = 'public-read';

	/** @var string */
	private $privacyImages = 'inherit';

	/** @var string */
	private $privacyVideo = 'inherit';

	/** @var string */
	private $privacyAudio = 'inherit';

	/** @var string */
	private $privacyDocs = 'inherit';

	/** @var array */
	private $ignoredMimeTypes = [];

	/** @var bool */
	private $uploadImages = true;

	/** @var bool */
	private $uploadAudio = true;

	/** @var bool */
	private $uploadVideo = true;

	/** @var bool */
	private $uploadDocuments = true;

	/** @var string|null */
	private $docCdn = null;

	/** @var string|null */
	private $cdn = null;

	/** @var string|null */
	private $signedCDN = null;

	/** @var bool */
	private $deleteOnUpload = false;

	/** @var bool */
	private $queuedDeletes = true;

	/** @var bool */
	private $deleteFromStorage = false;

	/** @var bool */
	private $cacheLookups = true;

	/** @var array */
	private $alternateFormatTypes = ['image/pdf', 'application/pdf', 'image/psd', 'application/vnd.adobe.illustrator'];

	/** @var null|array */
	private $allowedMimes = null;

	//endregion

	//region Constructor
	private function  __construct() {
		$this->deleteOnUpload = Environment::Option('mcloud-storage-delete-uploads', null, false);
		$this->deleteFromStorage = Environment::Option('mcloud-storage-delete-from-server', null, false);
		$this->queuedDeletes = Environment::Option('mcloud-storage-queue-deletes', null, false);
		$this->prefixFormat = Environment::Option('mcloud-storage-prefix', '');
		$this->subsitePrefixFormat = Environment::Option('mcloud-storage-subsite-prefixes', '', []);

		$this->uploadImages = Environment::Option('mcloud-storage-upload-images', null, true);
		$this->uploadAudio = Environment::Option('mcloud-storage-upload-audio', null, true);
		$this->uploadVideo = Environment::Option('mcloud-storage-upload-videos', null, true);
		$this->uploadDocuments = Environment::Option('mcloud-storage-upload-documents', null, true);

		if (StorageToolSettings::driver() === 'backblaze-s3') {
			$this->privacy = Environment::Option('mcloud-storage-backblaze-s3-privacy', null, "public-read");
			$this->privacyImages = 'inherit';
			$this->privacyAudio = 'inherit';
			$this->privacyVideo = 'inherit';
			$this->privacyDocs = 'inherit';
		} else {
			$this->privacy = Environment::Option('mcloud-storage-privacy', null, "public-read");
			$this->privacyImages = Environment::Option('mcloud-storage-privacy-images', null, "inherit");
			$this->privacyAudio = Environment::Option('mcloud-storage-privacy-audio', null, "inherit");
			$this->privacyVideo = Environment::Option('mcloud-storage-privacy-video', null, "inherit");
			$this->privacyDocs = Environment::Option('mcloud-storage-privacy-docs', null, "inherit");
		}

		if(!in_array($this->privacy, ['public-read', 'authenticated-read', 'private'])) {
			NoticeManager::instance()->displayAdminNotice('error', "Your AWS S3 settings are incorrect.  The ACL '{$this->privacy}' is not valid.  Defaulting to 'public-read'.");
			$this->privacy = 'public-read';
		}

		$this->cacheLookups = Environment::Option('mcloud-storage-cache-lookups', null, true);

		$ignored = Environment::Option('mcloud-storage-ignored-mime-types', null, '');
		$ignored_lines = explode("\n", $ignored);
		if(count($ignored_lines) <= 1) {
			$ignored_lines = explode(',', $ignored);
		}
		foreach($ignored_lines as $d) {
			if(!empty($d)) {
				$this->ignoredMimeTypes[] = trim($d);
			}
		}

		if (empty($this->uploadImages)) {
			$this->ignoredMimeTypes[] = 'image/*';
		}

		if (empty($this->uploadVideo)) {
			$this->ignoredMimeTypes[] = 'video/*';
		}

		if (empty($this->uploadAudio)) {
			$this->ignoredMimeTypes[] = 'audio/*';
		}

		if (empty($this->uploadDocuments)) {
			$this->ignoredMimeTypes[] = 'application/*';
			$this->ignoredMimeTypes[] = 'text/*';
		}


		$this->cdn = Environment::Option('mcloud-storage-cdn-base', 'ILAB_AWS_S3_CDN_BASE');
		if($this->cdn) {
			$this->cdn = rtrim($this->cdn, '/');
		}

		$this->signedCDN = Environment::Option('mcloud-storage-signed-cdn-base', null, null);
		if($this->signedCDN) {
			$this->signedCDN = rtrim($this->signedCDN, '/');
		}

		$this->docCdn = Environment::Option('mcloud-storage-doc-cdn-base', 'ILAB_AWS_S3_DOC_CDN_BASE', $this->cdn);

		$this->cacheControl = Environment::Option('mcloud-storage-cache-control', 'ILAB_AWS_S3_CACHE_CONTROL');

		$expires = Environment::Option('mcloud-storage-expires', 'ILAB_AWS_S3_EXPIRES');
		if(!empty($expires)) {
			$this->expires = gmdate('D, d M Y H:i:s \G\M\T', time() + ($expires * 60));
		}

		if (is_multisite() && is_network_admin() && empty($this->prefixFormat)) {
			$rootSiteName = get_network()->site_name;
			$adminUrl = network_admin_url('admin.php?page=media-cloud-settings&tab=storage#upload-handling');

			NoticeManager::instance()->displayAdminNotice('warning', "You are using Multisite WordPress but have not set a custom upload directory.  Your root site, <strong>'{$rootSiteName}'</strong> will be uploading to the root of your cloud storage which may not be desirable.  It's recommended that you set the Upload Directory to <code>sites/@{site-id}/@{date:Y/m}</code> in <a href='{$adminUrl}'>Cloud Storage</a> settings.", true, 'mcloud-multisite-missing-upload-path');
		}
	}

	/**
	 * @return StorageGlobals|null
	 */
	private static function instance() {
		if (!self::$instance) {
			self::$instance = new StorageGlobals();
		}

		return self::$instance;
	}
	//endregion

	//region Settings Properties
	/** @return string|null */
	public static function prefixFormat() {
		if (is_multisite()) {
			$blogId = get_current_blog_id();
			if (isset(self::instance()->subsitePrefixFormat[$blogId])) {
				return self::instance()->subsitePrefixFormat[$blogId];
			}
		}

		return self::instance()->prefixFormat;
	}

	/**
	 * @param int|null $id
	 * @return string
	 */
	public static function prefix($id = null) {
		return Prefixer::Parse(self::prefixFormat(), $id);
	}

	/** @return string|null */
	public static function cacheControl() {
		return self::instance()->cacheControl;
	}

	/** @return string|null */
	public static function expires() {
		return self::instance()->expires;
	}

	/** @return string */
	public static function privacy($type = null) {
		/** @var StorageGlobals $instance */
		$instance = self::instance();

		if ($type === null) {
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

	/** @return array */
	public static function ignoredMimeTypes() {
		return self::instance()->ignoredMimeTypes;
	}

	/** @return bool */
	public static function mimeTypeIsIgnored($mimeType, $additionalTypes = []) {
		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);

		$ignored = array_merge(self::instance()->ignoredMimeTypes, $additionalTypes);

		foreach($ignored as $mimeTypeTest) {
			if ($mimeType == $mimeTypeTest) {
				return true;
			}

			if (strpos($mimeTypeTest, '*') !== false) {
				$mimeTypeRegex = str_replace('*', '[aA-zZ0-9_.-]+', $mimeTypeTest);
				$mimeTypeRegex = str_replace('/','\/', $mimeTypeRegex);

				if (preg_match("/{$mimeTypeRegex}/m", $mimeType)) {
					if (self::uploadImages() && $altFormatsEnabled && (in_array($mimeType, self::alternativeFormatTypes()))) {
						return false;
					}

					return true;
				}
			}
		}
	}

	public static function allowedMimeTypes() {
		if (self::instance()->allowedMimes != null) {
			return self::instance()->allowedMimes;
		}

		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);
		$allowed = get_allowed_mime_types();

		if (self::uploadImages() && $altFormatsEnabled) {
			$allowed = array_merge($allowed, self::alternativeFormatTypes());
		}

		$result = [];
		foreach($allowed as $mime) {
			if (self::mimeTypeIsIgnored($mime)) {
				continue;
			}

			$result[] = $mime;
		}

		self::instance()->allowedMimes = $result;

		return $result;
	}

	/** @return bool */
	public static function uploadDocuments() {
		return self::instance()->uploadDocuments;
	}

	/** @return bool */
	public static function uploadImages() {
		return self::instance()->uploadImages;
	}

	/** @return bool */
	public static function uploadAudio() {
		return self::instance()->uploadAudio;
	}

	/** @return bool */
	public static function uploadVideo() {
		return self::instance()->uploadVideo;
	}

	/** @return string|null */
	public static function docCdn() {
		return self::instance()->docCdn;
	}

	/** @return string|null */
	public static function cdn() {
		return self::instance()->cdn;
	}

	/** @return string|null */
	public static function signedCDN() {
		return self::instance()->signedCDN;
	}

	/** @return bool */
	public static function deleteOnUpload() {
		return self::instance()->deleteOnUpload;
	}

	/** @return bool */
	public static function queuedDeletes() {
		$canQueue = apply_filters('media-cloud/storage/queue-deletes', true);
		Logger::info("Filter returned: '$canQueue'", [], __METHOD__, __LINE__);
		return (!empty($canQueue) && self::instance()->queuedDeletes);
	}

	/** @return bool */
	public static function deleteFromStorage() {
		return self::instance()->deleteFromStorage;
	}

	/** @return bool */
	public static function cacheLookups() {
		return self::instance()->cacheLookups;
	}

	/**
	 * @return array
	 */
	public static function alternativeFormatTypes() {
		return self::instance()->alternateFormatTypes;
	}
	//endregion

	//region Migration

	public static function migrateFromOtherPlugin() {
		$beenHereBefore = get_option('mcloud-other-plugins-migrated');
		if (!empty($beenHereBefore)) {
			return;
		}

		$migratedFrom = null;
		$migrated = false;
		$statelessVersion = get_option('wp-stateless-current-version');
		if (!empty($statelessVersion)) {
			$migrated = static::migrateStatelessSettings();
			$migratedFrom = 'WP-Stateless';
		} else {
			$migrated = static::migrateOffloadSettings();
			$migratedFrom = 'WP Offload Media';
		}

		if (!empty($migrated)) {
			Environment::UpdateOption('mcloud-tool-enabled-storage', true);
			NoticeManager::instance()->displayAdminNotice('info', "Media Cloud noticed you were using {$migratedFrom} and has migrated your settings automatically.  Everything should be working as before, but make sure to double check your Cloud Storage settings.", true, 'mcloud-migrated-other-plugin', 'forever');
			update_option('mcloud-other-plugins-did-migrate', $migratedFrom);
		}

		update_option('mcloud-other-plugins-migrated', true);
	}

	//endregion

	//region Stateless Migrations

	private static function migrateStatelessSettings() {
		$jsonConfigData = get_option('sm_key_json');
		if (empty($jsonConfigData)) {
			return false;
		}

		$bucket = get_option('sm_bucket');
		if (empty($bucket)) {
			return false;
		}

		$mode = get_option('sm_mode', 'cdn');
		if ($mode === 'disabled') {
			return false;
		}

		Environment::UpdateOption('mcloud-storage-provider', 'google');
		Environment::UpdateOption('mcloud-storage-google-credentials', $jsonConfigData);
		Environment::UpdateOption('mcloud-storage-google-bucket', $bucket);

		$deleteUploads = ($mode === 'stateless');
		if (!empty($deleteUploads)) {
			Environment::UpdateOption("mcloud-storage-delete-uploads", true);
		}

		$cdn = get_option('sm_custom_domain');
		if (!empty($cdn)) {
			Environment::UpdateOption("mcloud-storage-cdn-base", $cdn);
		}

		$uploadDir = trim(get_option('sm_root_dir'), '/');
		if (!empty($uploadDir)) {
			Environment::UpdateOption("mcloud-storage-prefix", $uploadDir);
		}

		$cacheControl = get_option('sm_cache_control');
		if (!empty($cacheControl)) {
			Environment::UpdateOption("mcloud-storage-cache-control", $cacheControl);
		}

		return true;
	}

	//endregion

	//region Maintenance

	public static function reloadSettings() {
		self::$instance = new StorageGlobals();
	}

	//endregion

	//region Offload Migrations

	private static function migrateOffloadMiscSettings($offloadConfig) {
		$deleteUploads = arrayPath($offloadConfig, 'remove-local-file', false);
		$cdn = arrayPath($offloadConfig, 'cloudfront', null);
		$prefix = arrayPath($offloadConfig, 'object-prefix', null);
		$usePrefix = arrayPath($offloadConfig, 'enable-object-prefix', false);

		if (!empty($deleteUploads)) {
			Environment::UpdateOption("mcloud-storage-delete-uploads", true);
		}

		if (!empty($cdn)) {
			Environment::UpdateOption("mcloud-storage-cdn-base", 'https://'.$cdn);
		}

		if (!empty($prefix) && !empty($usePrefix)) {
			$prefix = rtrim($prefix, '/');

			$useYearMonth = arrayPath($offloadConfig, 'use-yearmonth-folders', false);
			if (!empty($useYearMonth)) {
				$prefix = trailingslashit($prefix).'@{date:Y/m}';
			}

			$useVersioning = arrayPath($offloadConfig, 'object-versioning', false);
			if ($useVersioning) {
				$prefix = trailingslashit($prefix).'@{versioning}';
			}

			Environment::UpdateOption("mcloud-storage-prefix", $prefix);
		}
	}

	private static function migrateOffload($provider, $key, $secret) {
		$offloadConfig = get_option('tantan_wordpress_s3');
		$bucket = arrayPath($offloadConfig, 'bucket', null);
		$region = arrayPath($offloadConfig, 'region', 'auto');
		if (empty($bucket)) {
			return false;
		}

		Environment::UpdateOption('mcloud-storage-provider', $provider);
		Environment::UpdateOption("mcloud-storage-s3-access-key", $key);
		Environment::UpdateOption("mcloud-storage-s3-secret", $secret);
		Environment::UpdateOption("mcloud-storage-s3-bucket", $bucket);

		if ($provider === 'do') {
			Environment::UpdateOption("mcloud-storage-s3-endpoint", "https://{$region}.digitaloceanspaces.com");
		} else {
			Environment::UpdateOption("mcloud-storage-s3-region", $region);
		}

		static::migrateOffloadMiscSettings($offloadConfig);

		return true;
	}

	private static function migrateOffloadFromConfig($data) {
		$provider = arrayPath($data, 'provider', null);
		if (empty($provider) || !in_array($provider, ['aws', 'do', 'gcp'])) {
			return false;
		}

		$providerMap = ['aws' => 's3', 'do' => 'do', 'gcp' => 'google'];
		$provider = $providerMap[$provider];

		if ($provider !== 'google') {
			$key = arrayPath($data, 'access-key-id');
			$secret = arrayPath($data, 'secret-access-key');

			return static::migrateOffload($provider, $key, $secret);
		}

		$keyPath = arrayPath($data, 'key-file-path', null);
		if (!empty($keyPath)  && file_exists($keyPath)) {
			$googleConfig = file_get_contents($keyPath);
		} else {
			$googleConfigData = arrayPath($data, 'key-file', null);
			if (empty($googleConfigData) || !is_array($googleConfigData)) {
				return false;
			}

			$googleConfig = json_encode($googleConfigData, JSON_PRETTY_PRINT);
		}

		if (empty($googleConfig)) {
			return false;
		}

		$offloadConfig = get_option('tantan_wordpress_s3');
		$bucket = arrayPath($offloadConfig, 'bucket', null);
		if (empty($bucket)) {
			return false;
		}

		Environment::UpdateOption('mcloud-storage-provider', 'google');
		Environment::UpdateOption('mcloud-storage-google-credentials', $googleConfig);
		Environment::UpdateOption('mcloud-storage-google-bucket', $bucket);

		static::migrateOffloadMiscSettings($offloadConfig);

		return true;
	}

	public static function migrateOffloadSettings() {
		$migrated = false;
		if (defined('AS3CF_SETTINGS')) {
			$data = unserialize(constant('AS3CF_SETTINGS'));
			if (!empty($data)) {
				$migrated =  static::migrateOffloadFromConfig($data);
			}
		} else {
			$offloadConfig = get_option('tantan_wordpress_s3');
			if (!empty($offloadConfig)) {
				$migrated =  static::migrateOffloadFromConfig($offloadConfig);
			}
		}

		return $migrated;
	}
	//endregion
}