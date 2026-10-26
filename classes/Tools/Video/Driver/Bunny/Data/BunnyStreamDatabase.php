<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Bunny\Data;

final class BunnyStreamDatabase {
	const DB_VERSION = '1.0.4';

	/**
	 * Insures the additional database tables are installed
	 */
	public static function init() {
		// Assets
		static::installVideosTable();
	}


	//region Install Database Tables

	protected static function installVideosTable() {
		$currentVersion = get_site_option('mcloud_bunnystream_videos_db_version');
		if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
			return;
		}

		global $wpdb;

		$tableName = $wpdb->base_prefix.'mcloud_bstream_videos';
		$charset = $wpdb->get_charset_collate();

		$columns = "id BIGINT AUTO_INCREMENT,
	video_id VARCHAR(255) NOT NULL,
	attachment_id BIGINT NOT NULL,
	status int NULL,
	private int NULL,
	subtitles text NULL,
	";

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
	KEY status (status),
	KEY attachment_id (attachment_id),
	KEY video_id (video_id(255))
) {$charset};";
		}

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$exists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
		if ($exists) {
			update_site_option('mcloud_bunnystream_videos_db_version', self::DB_VERSION);
		}
	}

	//endregion
}