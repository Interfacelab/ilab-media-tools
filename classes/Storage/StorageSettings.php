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

namespace ILAB\MediaCloud\Storage;

use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\Prefixer;

if (!defined('ABSPATH')) { header('Location: /'); die; }

final class StorageSettings {
	//region Class variables
	/** @var StorageSettings */
	private static $instance = null;

	/** @var string|null */
	private $prefixFormat = '';

	/** @var string|null */
	private $cacheControl = null;

	/** @var string|null */
	private $expires = null;

	/** @var string */
	private $privacy = 'public-read';

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

	/** @var bool */
	private $deleteOnUpload = false;

	/** @var bool */
	private $deleteFromStorage = false;

	/** @var array */
	private $alternateFormatTypes = ['image/pdf', 'application/pdf', 'image/psd', 'application/vnd.adobe.illustrator'];

	/** @var null|array */
	private $allowedMimes = null;

	//endregion

	//region Constructor
	private function  __construct() {
		$this->deleteOnUpload = Environment::Option('mcloud-storage-delete-uploads');
		$this->deleteFromStorage = Environment::Option('mcloud-storage-delete-from-server');
		$this->prefixFormat = Environment::Option('mcloud-storage-prefix', '');

		$this->uploadImages = Environment::Option('mcloud-storage-upload-images', null, true);
		$this->uploadAudio = Environment::Option('mcloud-storage-upload-audio', null, true);
		$this->uploadVideo = Environment::Option('mcloud-storage-upload-videos', null, true);
		$this->uploadDocuments = Environment::Option('mcloud-storage-upload-documents', null, true);

		$this->privacy = Environment::Option('mcloud-storage-privacy', null, "public-read");
		if(!in_array($this->privacy, ['public-read', 'authenticated-read'])) {
			NoticeManager::instance()->displayAdminNotice('error', "Your AWS S3 settings are incorrect.  The ACL '{$this->privacy}' is not valid.  Defaulting to 'public-read'.");
			$this->privacy = 'public-read';
		}

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
	 * @return StorageSettings|null
	 */
	private static function instance() {
		if (!self::$instance) {
			self::$instance = new StorageSettings();
		}

		return self::$instance;
	}
	//endregion

	//region Settings Properties
	/** @return string|null */
	public static function prefixFormat() {
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
	public static function privacy() {
		return self::instance()->privacy;
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

	/** @return bool */
	public static function deleteOnUpload() {
		return self::instance()->deleteOnUpload;
	}

	/** @return bool */
	public static function deleteFromStorage() {
		return self::instance()->deleteFromStorage;
	}

	/**
	 * @return array
	 */
	public static function alternativeFormatTypes() {
		return self::instance()->alternateFormatTypes;
	}
	//endregion
}