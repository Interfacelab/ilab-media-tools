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

namespace MediaCloud\Plugin\Tools\Storage;

use MediaCloud\Plugin\Utilities\Logging\Logger;

/**
 * Interface for creating the required tables
 */
final class StoragePostMap {
	const DB_VERSION = '1.0.0';
	const DB_KEY = 'mcloud_post_map_db_version';

	private static $installed = false;
	private static $cache = [];

	/**
	 * Insures the additional database tables are installed
	 */
	public static function init() {
		static::verifyInstalled();
	}

	//region Install Database Tables

	protected static function verifyInstalled() {
		if (static::$installed === true) {
			return true;
		}

		$currentVersion = get_site_option(self::DB_KEY);
		if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
			static::$installed = true;
			return true;
		}

		Logger::warning("Storage map version mismatch $currentVersion => ".self::DB_VERSION." = ".version_compare(self::DB_VERSION, $currentVersion, '<='));

		return static::installMapTable();
	}

	protected static function installMapTable() {
		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_post_map';

		$suppress = $wpdb->suppress_errors(true);
		$wpdb->query("drop table {$tableName}");
		$wpdb->suppress_errors($suppress);

		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
	id BIGINT AUTO_INCREMENT,
	post_id BIGINT NOT NULL,
	post_url VARCHAR(255) NOT NULL,
	PRIMARY KEY  (id),
	KEY post_id(post_id),
	KEY post_url(post_url(255))
) {$charset};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option(self::DB_KEY, self::DB_VERSION);
			static::$installed = true;
			return true;
		}

		static::$installed = false;
		return false;
	}

	//endregion

	//region Queries
	public static function uncachedAttachmentIdFromURL($url, $bucketName) {
		global $wpdb;

		$query = $wpdb->prepare("select ID from {$wpdb->posts} where post_type='attachment' and guid = %s order by ID desc limit 1", $url);
		$postId = $wpdb->get_var($query);

		if (empty($postId)) {
			$parsedUrl = parse_url($url);
			$path = ltrim($parsedUrl['path'], '/');

			if (!empty($bucketName) && (strpos($path, $bucketName) === 0)) {
				$path = ltrim(str_replace($bucketName,'', $path),'/');
			}

			$query = $wpdb->prepare("select ID from {$wpdb->posts} where post_type='attachment' and guid like %s order by ID desc limit 1", '%'.$path);
			$postId = $wpdb->get_var($query);
		}

		return $postId;
	}

	public static function attachmentIdFromURL($postId, $url, $bucketName) {
		if (!empty($postId)) {
			return $postId;
		}

		if (isset(static::$cache[$url])) {
			return static::$cache[$url];
		}

		if (!empty(StorageToolSettings::instance()->cacheLookups)) {
			global $wpdb;

			$tableName = $wpdb->base_prefix.'mcloud_post_map';

			if (static::verifyInstalled()) {
				$query = $wpdb->prepare("select post_id from {$tableName} where post_url = %s order by post_id desc limit 1", $url);
				$postId = (int)$wpdb->get_var($query);
				if (($postId === -1) || !empty($postId)) {
					return ($postId === -1) ? null : $postId;
				}
			}

			$postId = static::uncachedAttachmentIdFromURL($url, $bucketName);

			if (!empty($postId)) {
				static::$cache[$url] = $postId;
				if (static::$installed === true) {
					$wpdb->insert($tableName, ['post_id' => empty($postId) ? -1 : $postId, 'post_url' => $url], ['%d', '%s']);
				}
			}

			return $postId;
		}

		$postId = static::uncachedAttachmentIdFromURL($url, $bucketName);

		return $postId;
	}
	//endregion
}
