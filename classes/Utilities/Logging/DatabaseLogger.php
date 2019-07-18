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


namespace ILAB\MediaCloud\Utilities\Logging;

use ILAB\MediaCloud\Utilities\Environment;

/**
 * For logging to database
 *
 * @package ILAB\MediaCloud\Utilities
 */
class DatabaseLogger {
    /** @var bool  */
    private $enabled = false;

    /** @var null|string  */
    private $table = null;

    /** @var int  */
    private $limit = 1000;

    public function __construct() {
        $this->limit = (int)Environment::Option('mcloud-debug-max-database-entries', null, $this->limit);

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
     */
    public function log($channel, $level, $message, $context = '') {
        if (!$this->enabled) {
            return;
        }

        $message = ltrim($message);

        global $wpdb;

        $query = $wpdb->prepare("insert into {$this->table} (date, channel, level, message, context) values (%s, %s, %s, %s, %s)", current_time('mysql', true), $channel, $level, $message, $context);
        $wpdb->query($query);
    }
    //endregion

    //region Database

    /**
     * Insures the table exists
     */
    protected function insureTable() {
        global $wpdb;

        $this->table = $wpdb->prefix.'ilab_mc_logging';

        $tableSchema = <<<SQL
        create table {$this->table} (
          id bigint not null auto_increment,
          date datetime not null,
          channel varchar(32) not null,
          level varchar(16) not null,
          message text,
          context text,
          primary key(id)
        );
SQL;

        global $wpdb;

        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($this->table));

        $this->enabled = ($wpdb->get_var($query) == $this->table);

        if (!$this->enabled) {
            $wpdb->query($tableSchema);

            $this->enabled = ($wpdb->get_var($query) == $this->table);
        }
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
        fputcsv($fd, ["date", "channel", "level", "message", "context"]);

        $rows = $wpdb->get_results("select date, channel, level, message, context from {$this->table} order by id asc", ARRAY_N);

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

    public function getLogEntries($perPage = 50, $pageNumber = 1) {
        global $wpdb;

        $offset = ($pageNumber - 1) * $perPage;
        $query = "select * from {$this->table} order by id desc limit $perPage offset $offset";
        return $wpdb->get_results($query, ARRAY_A);
    }

    public function totalEntries() {
        global $wpdb;
        return $wpdb->get_var("select count(id) from {$this->table}");
    }

    // endregion
}