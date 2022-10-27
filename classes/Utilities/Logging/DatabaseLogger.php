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


namespace MediaCloud\Plugin\Utilities\Logging;

use MediaCloud\Plugin\Tools\Debugging\DebuggingToolSettings;
use MediaCloud\Plugin\Utilities\Environment;

/**
 * For logging to database
 *
 * @package MediaCloud\Plugin\Utilities
 */
class DatabaseLogger {
	const DB_VERSION = '1.0.5';

    /** @var bool  */
    private $enabled = false;

    /** @var null|string  */
    private $table = null;

    /** @var int  */
    private $limit = 1000;

    public function __construct() {
        $this->limit = (int)DebuggingToolSettings::instance()->maxDatabaseEntries;
        if (empty($this->limit)) {
        	$this->limit = 1000;
        }

        $this->insureTable();
        $this->pruneLog();
    }

    //region Logging

    /**
     * Logs to the database
     *
     * @param $channel
     * @param $level
     * @param $message
     * @param $context
     * @param $class
     * @param $method
     * @param $line
     */
    public function log($channel, $level, $message, $context = '', $class = null, $method = null, $line = null) {
        if (!$this->enabled) {
            return;
        }

        $message = ltrim($message);

        global $wpdb;

        $query = $wpdb->prepare("insert into {$this->table} (date, channel, level, message, context, class, method, line) values (%s, %s, %s, %s, %s, %s, %s, %d)", current_time('mysql', true), $channel, $level, $message, $context, $class, $method, $line);
        $wpdb->query($query);
    }
    //endregion

    //region Database

    /**
     * Insures the table exists
     */
    protected function insureTable() {
	    global $wpdb;
	    $this->table = $wpdb->base_prefix.'ilab_mc_logging';

	    $currentVersion = get_site_option('mcloud_logging_table_db_version');
	    if (!empty($currentVersion) && version_compare(self::DB_VERSION, $currentVersion, '<=')) {
		    $this->enabled = true;
		    return;
	    }

	    $charset = $wpdb->get_charset_collate();

        $tableSchema = "CREATE TABLE {$this->table} (
	id BIGINT AUTO_INCREMENT,
	date DATETIME NOT NULL,
	class VARCHAR(128),
	method VARCHAR(128),
	channel VARCHAR(32) NOT NULL,
	level VARCHAR(16) NOT NULL,
	message TEXT NULL,
	context TEXT NULL,
	line BIGINT NULL,
	PRIMARY KEY  (id)
) {$charset};";

	    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    $result = dbDelta($tableSchema);

	    $exists = ($wpdb->get_var("SHOW TABLES LIKE '$this->table'") == $this->table);
	    if ($exists) {
		    update_site_option('mcloud_logging_table_db_version', self::DB_VERSION);
	    }

	    $this->enabled = $exists;
    }

    /**
     * Prunes the table
     *
     * @param int $limit
     */
    public function pruneLog() {
        if (!$this->enabled) {
            return;
        }

        global $wpdb;
        $query = "delete from {$this->table} where id not in (select id from (select id from {$this->table} order by id desc limit {$this->limit}) foo)";
        $wpdb->query($query);
    }

    /**
     * Clears the log
     */
    public function clearLog() {
        if (!$this->enabled) {
            return;
        }

        global $wpdb;
        $wpdb->query("delete from {$this->table}");
    }

    // endregion

    // region Exporting

    /**
     * Exports the log to CSV
     *
     * @return bool|string
     */
    public function csv() {
        global $wpdb;

        $fd = fopen('php://temp/maxmemory:1048576', 'w');
        fputcsv($fd, ["date", "channel", "level", "class", "method", "line", "message", "context"]);

        $rows = $wpdb->get_results("select date, channel, level, class, method, line, message, context from {$this->table} order by id asc", ARRAY_N);

        if (is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                fputcsv($fd, $row);
            }
        }

        rewind($fd);
        $csv = stream_get_contents($fd);
        fclose($fd);

        return $csv;
    }

    // endregion

    // region Querying

    public function getLogEntries($logLevel = null, $class = null, $search = null, $perPage = 50, $pageNumber = 1) {
        global $wpdb;

        $offset = ($pageNumber - 1) * $perPage;
        $where = $this->buildWhere($logLevel, $class, $search);
        $query = "select * from {$this->table} {$where} order by id desc limit $perPage offset $offset";
        return $wpdb->get_results($query, ARRAY_A);
    }

    private function buildWhere($logLevel, $class, $search) {
    	$where = [];
    	$whereVals = [];

    	if (!empty($logLevel)) {
    		$where[] = 'level = %s';
    		$whereVals[] = $logLevel;
	    }

    	if (!empty($class)) {
    		if ($class === 'not-empty') {
    			$where[] = 'class is not null and class != \'\'';
		    } else if ($class === 'empty') {
			    $where[] = 'class is null or class = \'\'';
		    } else {
    			$where[] = 'class = %s';
    			$whereVals[] = $class;
		    }
	    }

    	if (!empty($search)) {
    		$where[] = 'message like %s';
    		$whereVals[] = '%'.$search.'%';
	    }

    	if (count($where) > 0) {
    		if (count($whereVals) > 0) {
			    $whereClause = ' where '.implode(' and ', $where);

			    global $wpdb;
			    return $wpdb->prepare($whereClause, $whereVals);
		    } else {
    			return ' where '.implode(' and ', $where);
		    }
	    }

    	return '';
    }

    public function totalEntries($logLevel = null, $class = null, $search = null) {
        global $wpdb;
        return $wpdb->get_var("select count(id) from {$this->table}".$this->buildWhere($logLevel, $class, $search));
    }

    public function getClasses() {
	    global $wpdb;
	    $query = "select distinct class from {$this->table} where class is not null and class != '' order by class asc";
	    return $wpdb->get_results($query, ARRAY_A);
    }

    // endregion
}