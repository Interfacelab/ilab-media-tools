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

namespace MediaCloud\Plugin\Tasks;

use MediaCloud\Plugin\Utilities\Logging\Logger;

/**
 * Interface for creating the required tables
 */
final class TaskDatabase {
	const DB_VERSION = '1.0.2';

	/**
	 * Insures the additional database tables are installed
	 */
	public static function init($force = false) {
		// Task
		static::installTaskTable($force);

		// TaskChunk
		static::installDataTable($force);

		// TaskSchedule
		static::installScheduleTable($force);

		// TaskSchedule
		static::installTokenTable($force);
	}


	//region Install Database Tables

	protected static function installTaskTable($force = false) {
		if (!$force) {
			$currentVersion = get_site_option('mcloud_task_table_db_version');
			if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
				return;
			}
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_task';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
	id BIGINT AUTO_INCREMENT,
	tuid VARCHAR(12) NOT NULL,
	type VARCHAR(255) NOT NULL,
	locked BIGINT NULL,
	cli INT DEFAULT 0 NOT NULL,
	shouldCancel INT DEFAULT 0 NOT NULL,
	state INT DEFAULT 0 NOT NULL,
	currentItem INT DEFAULT 0 NOT NULL,
	totalItems INT DEFAULT 0 NOT NULL,
	lastTime FLOAT NULL,
	startTime bigint NULL,
	endTime bigint NULL,
	lastRun bigint NULL,
	duration FLOAT NULL,
	timePer FLOAT NULL,
	memoryPer bigint NULL,
	currentItemID VARCHAR(512) NULL,
	currentTitle VARCHAR(512) NULL,
	currentFile VARCHAR(512) NULL,
	currentThumb VARCHAR(1024) NULL,
	isIcon INT DEFAULT 0 NOT NULL,
	errorMessage TEXT NULL,
	options TEXT NULL,
	PRIMARY KEY  (id)
) {$charset};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_task_table_db_version', self::DB_VERSION);
		}
	}

	protected static function installDataTable($force = false) {
		if (!$force) {
			$currentVersion = get_site_option('mcloud_task_data_db_version');
			if(!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
				return;
			}
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_task_data';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
	id BIGINT AUTO_INCREMENT,
	taskId BIGINT NOT NULL,
	current INT NOT NULL DEFAULT 0,
	complete INT NOT NULL DEFAULT 0,
	data TEXT NULL,
	PRIMARY KEY  (id)
) {$charset};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_task_data_db_version', self::DB_VERSION);
		}
	}

	protected static function installScheduleTable($force = false) {
		if (!$force) {
			$currentVersion = get_site_option('mcloud_task_schedule_db_version');
			if(!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
				return;
			}
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_task_schedule';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
	id BIGINT AUTO_INCREMENT,
	tuid VARCHAR(12) NOT NULL,
	recurring INT NOT NULL DEFAULT 0,
	lastRun BIGINT,
	nextRun BIGINT,
	schedule VARCHAR(256) NOT NULL,
	taskType VARCHAR(256) NOT NULL,
	options TEXT,
	selection TEXT,
	PRIMARY KEY  (id)
) {$charset};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_task_schedule_db_version', self::DB_VERSION);
		}
	}

	protected static function installTokenTable($force = false) {
		if (!$force) {
			$currentVersion = get_site_option('mcloud_task_token_db_version');
			if(!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
				return;
			}
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_task_token';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
	id BIGINT AUTO_INCREMENT,
	token VARCHAR(256) NOT NULL,
	value VARCHAR(256) NOT NULL,
	time bigint NOT NULL,
	PRIMARY KEY  (id)
) {$charset};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_task_token_db_version', self::DB_VERSION);
		}
	}

	private static function tableExists($tableName) {
		global $wpdb;
		$tableName = $wpdb->base_prefix.$tableName;
		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") === $tableName);
		return $exists;
	}

	public static function taskTableExists() {
		return self::tableExists('mcloud_task');
	}

	public static function taskDataTableExists() {
		return self::tableExists('mcloud_task_data');
	}

	public static function taskScheduleTableExists() {
		return self::tableExists('mcloud_task_schedule');
	}

	public static function taskTokenTableExists() {
		return self::tableExists('mcloud_task_token');
	}

	//endregion

	//region Nuke

	public static function nukeData() {
		global $wpdb;

		$wpdb->query("delete from {$wpdb->base_prefix}mcloud_task");
		$wpdb->query("delete from {$wpdb->base_prefix}mcloud_task_data");
		$wpdb->query("delete from {$wpdb->base_prefix}mcloud_task_schedule");
		$wpdb->query("delete from {$wpdb->base_prefix}mcloud_task_token");
	}

	//endregion

	//region Tokens

	public static function setToken($token, $tokenVal) {
		global $wpdb;
		$tableName = $wpdb->base_prefix.'mcloud_task_token';

		$wpdb->insert($tableName, ['token' => $token, 'value' => $tokenVal, 'time' => time()]);
	}

	public static function verifyToken($token, $tokenVal) {
		global $wpdb;
		$tableName = $wpdb->base_prefix.'mcloud_task_token';

		$val = $wpdb->get_var($wpdb->prepare("select value from {$tableName} where token = %s", $token));
		return ($tokenVal == $val);
	}

	public static function deleteToken($token) {
		Logger::info("Deleting token $token", [], __METHOD__, __LINE__);

		global $wpdb;
		$tableName = $wpdb->base_prefix.'mcloud_task_token';
		$wpdb->delete($tableName, ['token' => $token]);

		static::deleteOldTokens();
	}

	public static function deleteOldTokens() {
		global $wpdb;
		$tableName = $wpdb->base_prefix.'mcloud_task_token';
		$wpdb->query($wpdb->prepare("delete from {$tableName} where time <= %d", time() - (60 * 60 * 24)));
	}
	//endregion
}