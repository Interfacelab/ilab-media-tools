<?php


namespace MediaCloud\Plugin\Tools\Video\Driver\Mux;


use MediaCloud\Plugin\Utilities\Logging\Logger;

class MuxEventData {
	const DB_VERSION = '1.0.1';
	const DB_KEY = 'mcloud_mux_events_db_version';
	const DB_TABLE = 'mcloud_mux_events';

	private static $installed = false;

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
		if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '==')) {
			global $wpdb;

			$tableName = $wpdb->base_prefix.self::DB_TABLE;
			$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
			if ($exists) {
				static::$installed = true;
				return true;
			}
		}

		return static::installTable();
	}

	protected static function installTable() {
		global $wpdb;

		$tableName = $wpdb->base_prefix.self::DB_TABLE;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
	id BIGINT AUTO_INCREMENT,
	time bigint NOT NULL,
	post_id BIGINT NOT NULL,
	event VARCHAR(255) NOT NULL,
	data TEXT NULL,
	PRIMARY KEY  (id),
	KEY post_id(post_id)
) {$charset};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$result = dbDelta($sql);

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

	//region Data
	public static function recordEvent($postId, $event) {
		Logger::info("Mux: Record event {$event} for {$postId}", [], __METHOD__, __LINE__);

		if (static::verifyInstalled()) {
			global $wpdb;

			$tableName = $wpdb->base_prefix.self::DB_TABLE;

			$wpdb->insert($tableName, ['post_id' => $postId, 'time' => time(), 'event' => $event], ['%d', '%d', '%s']);
		}
	}

	public static function deleteEvents($postId) {
		if (static::verifyInstalled()) {
			global $wpdb;

			$tableName = $wpdb->base_prefix.self::DB_TABLE;

			$wpdb->delete($tableName, ['post_id' => $postId], ['%d']);
		}
	}

	public static function totalEventCount(int $postId) {
		if (static::verifyInstalled()) {
			global $wpdb;

			$tableName = $wpdb->base_prefix.self::DB_TABLE;

			return $wpdb->get_var("select count(id) from $tableName where post_id = $postId");
		}

		return 0;
	}

	public static function events(int $postId, int $count, int $offset) {
		if (static::verifyInstalled()) {
			global $wpdb;

			$tableName = $wpdb->base_prefix.self::DB_TABLE;

			return $wpdb->get_results("select * from $tableName where post_id = $postId  order by time desc limit $count offset $offset", ARRAY_A);
		}

		return [];
	}
	//endregion
}