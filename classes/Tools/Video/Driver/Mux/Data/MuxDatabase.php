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

namespace MediaCloud\Plugin\Tools\Video\Driver\Mux\Data;

/**
 * Interface for creating the required tables
 */
final class MuxDatabase {
	const DB_VERSION = '1.0.0';

	/**
	 * Insures the additional database tables are installed
	 */
	public static function init() {
		// Assets
		static::installAssetsTable();
		static::installPlaybackIDsTable();
		static::installRenditionsTable();
	}


	//region Install Database Tables

	protected static function installAssetsTable() {
		$currentVersion = get_site_option('mcloud_mux_assets_db_version');
		if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
			return;
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_mux_assets';
		$charset = $wpdb->get_charset_collate();

		$columns = "id BIGINT AUTO_INCREMENT,
	muxId VARCHAR(255) NOT NULL,
	status VARCHAR(32) NULL,
	title VARCHAR(255) NULL,
	attachmentId bigint NULL,
	createdAt bigint NULL,
	duration float NULL,
	frameRate float NULL,
	mp4Support int NOT NULL default 0,
	width int NOT NULL default 0,
	height int NOT NULL default 0,
	aspectRatio VARCHAR(32) NULL,
	jsonData text NULL,";

		$rowFormat = $wpdb->get_var("SELECT @@innodb_default_row_format;");
		if (in_array(strtolower($rowFormat), ['redundant', 'compact'])) {
			$sql = "CREATE TABLE {$tableName} (
    {$columns}
	PRIMARY KEY  (id)
) {$charset};";
		} else {
			$sql = "CREATE TABLE {$tableName} (
	{$columns}
	PRIMARY KEY  (id),
	KEY status (status(32)),
	KEY muxId (muxId(255))
) {$charset};";
		}

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_mux_assets_db_version', self::DB_VERSION);
		}
	}

	protected static function installPlaybackIDsTable() {
		$currentVersion = get_site_option('mcloud_mux_playback_db_version');
		if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
			return;
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_mux_playback';
		$charset = $wpdb->get_charset_collate();

		$columns = "id BIGINT AUTO_INCREMENT,
	muxId VARCHAR(255) NOT NULL,
	playbackId VARCHAR(255) NOT NULL,
	policy VARCHAR(16) NOT NULL,";

		$rowFormat = $wpdb->get_var("SELECT @@innodb_default_row_format;");
		if (in_array(strtolower($rowFormat), ['redundant', 'compact'])) {
			$sql = "CREATE TABLE {$tableName} (
	{$columns}
	PRIMARY KEY  (id)
) {$charset};";
		} else {
			$sql = "CREATE TABLE {$tableName} (
	{$columns}
	PRIMARY KEY  (id),
	KEY playbackId (playbackId(255)),
	KEY policy (policy(16)),
	KEY muxId (muxId(255))
) {$charset};";
		}

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_mux_playback_db_version', self::DB_VERSION);
		}
	}

	protected static function installRenditionsTable() {
		$currentVersion = get_site_option('mcloud_mux_renditions_db_version');
		if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
			return;
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_mux_renditions';
		$charset = $wpdb->get_charset_collate();

		$columns = "id BIGINT AUTO_INCREMENT,
	muxId VARCHAR(255) NOT NULL,
	rendition VARCHAR(16) NOT NULL,
	width int null,
	height int null,
	bitrate int null,
	filesize int null,";

		$rowFormat = $wpdb->get_var("SELECT @@innodb_default_row_format;");
		if (in_array(strtolower($rowFormat), ['redundant', 'compact'])) {
			$sql = "CREATE TABLE {$tableName} (
	{$columns}
	PRIMARY KEY  (id)
) {$charset};";
		} else {
			$sql = "CREATE TABLE {$tableName} (
	{$columns}
	PRIMARY KEY  (id),
	KEY rendition (rendition(16)),
	KEY muxId (muxId(255))
) {$charset};";
		}

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_mux_renditions_db_version', self::DB_VERSION);
		}
	}
	//endregion
}