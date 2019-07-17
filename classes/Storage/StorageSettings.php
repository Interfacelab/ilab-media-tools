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
	private $uploadDocuments = true;

	/** @var string|null */
	private $docCdn = null;

	/** @var string|null */
	private $cdn = null;

	/** @var bool */
	private $deleteOnUpload = false;

	/** @var bool */
	private $deleteFromStorage = false;
	//endregion

	//region Constructor
	private function  __construct() {
		$this->deleteOnUpload = Environment::Option('mcloud-storage-delete-uploads');
		$this->deleteFromStorage = Environment::Option('mcloud-storage-delete-from-server');
		$this->prefixFormat = Environment::Option('mcloud-storage-prefix', '');
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
	public static function uploadDocuments() {
		return self::instance()->uploadDocuments;
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
	//endregion
}