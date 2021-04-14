<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Utilities;

use MediaCloud\Plugin\Utilities\Logging\Logger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class Prefixer
 * @package MediaCloud\Plugin\Utilities
 */
final class Prefixer {
	//region Class Variables
	/** @var Prefixer */
	private static $instance = null;

	/** @var array */
	private $versionedIds = [];

	private $previousVersion = null;
	private $currentVersion = null;

	private $currentType = null;
	//endregion

	//region Constructor/Static Instance
	private function __construct() {
		$this->updateVersion();
	}

	/**
	 * Returns the static instance of the prefixer.
	 *
	 * @return Prefixer
	 */
	private static function instance() {
		if (!self::$instance) {
			self::$instance = new Prefixer();
		}

		return self::$instance;
	}
	//endregion

	//region Prefix
	/**
	 * Generates a UUID string.
	 * @return string
	 */
	private function genUUID() {
		return sprintf('%04x%04x%04x%03x4%04x%04x%04x%04x',
		               mt_rand(0, 65535),
		               mt_rand(0, 65535),
		               mt_rand(0, 65535),
		               mt_rand(0, 4095),
		               bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
		               mt_rand(0, 65535),
		               mt_rand(0, 65535),
		               mt_rand(0, 65535)
		);
	}

	/**
	 * Generates a UUID path
	 * @return string
	 */
	private function genUUIDPath() {
		$uid = $this->genUUID();
		$result = '/';

		$segments = 8;
		if($segments > strlen($uid) / 2) {
			$segments = strlen($uid) / 2;
		}
		for($i = 0; $i < $segments; $i ++) {
			$result .= substr($uid, $i * 2, 2).'/';
		}

		return $result;
	}

	/**
	 * Updates the current version
	 */
	private function updateVersion() {
		$this->previousVersion = $this->currentVersion;

		$date_format = 'dHis';
		// Use current time so that object version is unique
		$time = current_time('timestamp');

		$object_version = date($date_format, $time).'/';
		$object_version = apply_filters('as3cf_get_object_version_string', $object_version);
		$this->currentVersion = $object_version;

		Logger::info("Set new version: {$this->currentVersion}", [], __METHOD__, __LINE__);
	}

	/**
	 * Restores the current version
	 */
	private function restoreVersion() {
		if (!empty($this->previousVersion)) {
			$this->currentVersion = $this->previousVersion;
			$this->previousVersion = null;

			Logger::info("Restored version: {$this->currentVersion}", [], __METHOD__, __LINE__);
		} else {
			Logger::info("No previous version to restore", [], __METHOD__, __LINE__);
		}
	}

	/**
	 * Generates a prefix string from a prefix format string.
	 *
	 * @param string $prefix
	 * @param int|null $id
	 *
	 * @return string
	 */
	private function parsePrefix($prefix, $id = null) {
		$host = parse_url(get_home_url(), PHP_URL_HOST);

		$user = wp_get_current_user();
		$userName = '';
		if($user->ID != 0) {
			$userName = sanitize_title($user->display_name);
		}

		$prefix = str_replace("@{versioning}", $this->currentVersion, $prefix);
		$prefix = str_replace("@{site-id}", sanitize_title(strtolower(get_current_blog_id())), $prefix);
		$prefix = str_replace("@{site-name}", sanitize_title(strtolower(get_bloginfo('name'))), $prefix);
		$prefix = str_replace("@{site-host}", $host, $prefix);
		$prefix = str_replace("@{user-name}", $userName, $prefix);
		$prefix = str_replace("@{unique-id}", $this->genUUID(), $prefix);
		$prefix = str_replace("@{unique-path}", $this->genUUIDPath(), $prefix);
		$prefix = str_replace('@{type}', $this->currentType, $prefix);
		$prefix = str_replace("//", "/", $prefix);

		$matches = [];
		preg_match_all('/\@\{date\:([^\}]*)\}/', $prefix, $matches);
		if(count($matches) == 2) {
			for($i = 0; $i < count($matches[0]); $i ++) {
				$prefix = str_replace($matches[0][$i], date($matches[1][$i]), $prefix);
			}
		}

		$prefix = apply_filters('media-cloud/storage/prefix', $prefix, $id);
		$prefix = trim($prefix, '/').'/';

		Logger::info("Generated prefix: {$prefix}", [], __METHOD__, __LINE__);

		return $prefix;
	}

	/**
	 * Generates a prefix string from a prefix format string.
	 *
	 * @param string|null $prefixFormat
	 * @param int|null $id
	 *
	 * @return string
	 */
	public static function Parse($prefixFormat, $id = null) {
		if (!empty($prefixFormat)) {
			return self::instance()->parsePrefix($prefixFormat, $id);
		} else {
			$wpUpload = wp_upload_dir();
			return ltrim(trailingslashit($wpUpload['subdir']), '/');
		}
	}

	public static function nextVersion() {
		self::instance()->updateVersion();
	}

	public static function previousVersion() {
		self::instance()->restoreVersion();
	}

	/**
	 * @param false|null|string $type
	 */
	public static function setType($type) {
		if ($type != null) {
			$typeParts = explode('/', $type);
			$type = $typeParts[0];
			if ($type == 'application') {
				$type = 'doc';
			}
		}

		self::instance()->currentType = $type;
	}

	public static function currentType() {
		return self::instance()->currentType;
	}
	//endregion
}
