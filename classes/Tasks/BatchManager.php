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

namespace ILAB\MediaCloud\Tasks;

use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use function ILAB\MediaCloud\Utilities\json_response;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;

/**
 * Manages the batch import tools.
 *
 * @package ILAB\MediaCloud\Tasks
 */
final class BatchManager {
    private static $batchClasses = [];
    private static $instance = null;


    private function __construct() {
        add_action('wp_ajax_ilab_batch_test', [$this, 'testAccess']);
        add_action('wp_ajax_nopriv_batch_test', [$this, 'testAccess']);
    }

    /**
     * Returns the current instance of the BatchManager
     *
     * @return BatchManager
     */
    public static function instance() {
        if (empty(static::$instance)) {
            static::$instance = new BatchManager();
        }

        return static::$instance;
    }

    //region Static Methods

    /**
     * Registers a BackgroundProcess class for a specific batch type
     *
     * @param $batch
     * @param $className
     */
    public static function registerBatchClass($batch, $className) {
        static::$batchClasses[$batch] = $className;
    }

    /**
     * Installs the cron task to make sure that batches continue to process, but if WP CRON is disabled (which it
     * should be), this will force the batches to run every 60 seconds.
     */
    public static function boot() {
        add_action('init', function(){
            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
                BatchManager::instance()->dispatchBatchesIfNeeded();
            } else {
                add_filter('cron_schedules', function($schedules) {
                    if (isset($schedules['1min'])) {
                        return $schedules;
                    }

                    $schedules['1min'] = [
                        'interval' => 60,
                        'display' => 'Once every minute'
                    ];

                    return $schedules;
                });

                add_action('ilab_media_tools_run_batch_hook', function(){
                    BatchManager::instance()->dispatchBatchesIfNeeded();
                });

                if (!wp_next_scheduled('ilab_media_tools_run_batch_hook')) {
                    wp_schedule_event(time(), '1min', 'ilab_media_tools_run_batch_hook');
                }
            }
        });
    }

    //endregion

    //region Properties

    /**
     * Returns the current status (true = running, false = not running) for a given batch
     * @param string $batch
     * @return bool
     */
    public function status($batch) {
        return get_option("ilab_media_tools_{$batch}_status", false);
    }

    /**
     * Sets the status for a given batch
     * @param string $batch
     * @param bool $status
     */
    public function setStatus($batch, $status) {
        update_option("ilab_media_tools_{$batch}_status", $status);
    }

    /**
     * Returns the index of the item currently being processed
     * @param $batch
     * @return int
     */
    public function current($batch) {
        return get_option("ilab_media_tools_{$batch}_current", 0);
    }

    /**
     * Sets the current index being processed
     * @param $batch
     * @param int $index
     */
    public function setCurrent($batch, $index) {
        update_option("ilab_media_tools_{$batch}_current", $index);
    }

    /**
     * Returns the ID of the item currently being processed
     * @param $batch
     * @return int
     */
    public function currentID($batch) {
        return get_option("ilab_media_tools_{$batch}_current_id", 0);
    }

    /**
     * Sets the current index being processed
     * @param $batch
     * @param int $index
     */
    public function setCurrentID($batch, $id) {
        update_option("ilab_media_tools_{$batch}_current_id", $id);
    }

    /**
     * Returns the file name of the current item being processed
     * @param $batch
     * @return string|null
     */
    public function currentFile($batch) {
        return get_option("ilab_media_tools_{$batch}_file");
    }

    /**
     * Sets the current file being processed
     * @param $batch
     * @param string|null $file
     */
    public function setCurrentFile($batch, $file) {
        update_option("ilab_media_tools_{$batch}_last_update", microtime(true));
        update_option("ilab_media_tools_{$batch}_file", $file);
    }

    /**
     * Returns the count of items in this batch being processed
     * @param $batch
     * @return int
     */
    public function totalCount($batch) {
        return get_option("ilab_media_tools_{$batch}_total", 0);
    }

    /**
     * Sets the count of total items to be processed by this batch
     * @param $batch
     * @param int $count
     */
    public function setTotalCount($batch, $count) {
        update_option("ilab_media_tools_{$batch}_total", $count);
    }

    /**
     * Returns a bool flag determining if this batch should stop processing.
     * @param $batch
     * @return bool
     */
    public function shouldCancel($batch) {
        return get_option("ilab_media_tools_{$batch}_should_cancel", false);
    }

    /**
     * Sets the flag determining if this batch should be cancelled
     * @param $batch
     * @param $cancel
     */
    public function setShouldCancel($batch, $cancel) {
        update_option("ilab_media_tools_{$batch}_should_cancel", $cancel);
    }

    /**
     * Returns the time this batch was last run
     * @param $batch
     * @return int
     */
    public function lastRun($batch) {
        return get_option("ilab_media_tools_{$batch}_last_run", time());
    }

    /**
     * Sets the time this batch was last run
     * @param $batch
     * @param $time
     */
    public function setLastRun($batch, $time) {
        update_option("ilab_media_tools_{$batch}_last_run", $time);
    }

    /**
     * Sets the time this batch was last run to the current time
     * @param $batch
     */
    public function setLastRunToNow($batch) {
        $this->setLastRun($batch, time());
    }

    /**
     * Returns the amount of time the last item took to process in seconds
     * @param $batch
     * @return float
     */
    public function lastTime($batch) {
        return get_option("ilab_media_tools_{$batch}_last_time", 0);
    }

    /**
     * Returns the total processing time for this batch
     * @param $batch
     * @return float
     */
    public function totalTime($batch) {
        return get_option("ilab_media_tools_{$batch}_total_time", 0);
    }

    /**
     * Increments the total processing time for the batch
     * @param $batch
     * @param float $lastTime
     */
    public function incrementTotalTime($batch, $lastTime) {
        update_option("ilab_media_tools_{$batch}_last_time", $lastTime);
        update_option("ilab_media_tools_{$batch}_total_time", $this->totalTime($batch) + $lastTime);
    }

    /**
     * Sets the current error
     * @param $batch
     * @param $error
     */
    public function setErrorMessage($batch, $error) {
        update_option("ilab_media_tools_{$batch}_error_id", $batch.'-error-'.sanitize_title(microtime(true)).'-forever');
        update_option("ilab_media_tools_{$batch}_error_message", $error);
    }

    /**
     * Returns the current error message
     * @param $batch
     * @return mixed|void
     */
    public function errorMessage($batch) {
        return get_option("ilab_media_tools_{$batch}_error_message", null);
    }

    /**
     * Returns the current message id
     *
     * @param $batch
     * @return mixed|void
     */
    public function errorMessageId($batch) {
        return get_option("ilab_media_tools_{$batch}_error_id", null);
    }

    /**
     * Returns the amount of time that has elapsed since the last item was processed.
     * @param $batch
     * @return float
     */
    public function lastUpdate($batch) {
        $lu = get_option("ilab_media_tools_{$batch}_last_update", 0);
        if ($lu > 0) {
            $lu = microtime(true) - $lu;
        }

        return $lu;
    }

    //endregion

    /**
     * Returns stats about this batch
     *
     * @param $batch
     * @return array
     */
    public function stats($batch) {
        $total = $this->totalCount($batch);
        $current = $this->current($batch);
        $totalTime = $this->totalTime($batch);

        $progress = 0;
        if ($total > 0) {
            $progress = ($current / $total) * 100;
        }

        $postsPerMinute = 0;
        $eta = 0;
        if (($totalTime > 0) && ($current > 1)) {
            $postsPerSecond = ($totalTime / ($current - 1));
            if ($postsPerSecond > 0) {
                $postsPerMinute = 60 / $postsPerSecond;
                $eta = ($total - $current) / $postsPerMinute;
            }
        }



        $thumbUrl = null;
        $icon = false;

        if (!empty($this->currentID($batch))) {
            $thumb = wp_get_attachment_image_src($this->currentID($batch), 'thumbnail', true);
            if (!empty($thumb)) {
                $thumbUrl = $thumb[0];
                $icon = (($thumb[1] != 150) && ($thumb[2] != 150));
            }
        }

        return [
            'running' => $this->status($batch),
            'current' => $current,
            'currentID' => $this->currentID($batch),
            'thumb' => $thumbUrl,
            'icon' => $icon,
            'currentFile' => $this->currentFile($batch),
            'total' => $total,
            'totalTime' => $totalTime,
            'lastTime' => $this->lastTime($batch),
            'lastRun' => $this->lastRun($batch),
            'lastUpdate' => $this->lastUpdate($batch),
            'eta' => $eta,
            'progress' => $progress,
            'postsPerMinute' => $postsPerMinute,
            'shouldCancel' => $this->shouldCancel($batch)
        ];
    }

    /**
     * Display any error for a batch
     * @param $batch
     */
    public function displayAnyErrors($batch) {
        $error = $this->errorMessage($batch);

        if (!empty($error)) {
            NoticeManager::instance()->displayAdminNotice('error', $error, true, $this->errorMessageId($batch));
        }
    }

    /**
     * Removes an stored information about the batch
     * @param $batch
     */
    public function reset($batch) {
        delete_option("ilab_media_tools_{$batch}_status");
        delete_option("ilab_media_tools_{$batch}_current");
        delete_option("ilab_media_tools_{$batch}_file");
        delete_option("ilab_media_tools_{$batch}_total");
        delete_option("ilab_media_tools_{$batch}_last_run");
        delete_option("ilab_media_tools_{$batch}_total_time");
        delete_option("ilab_media_tools_{$batch}_last_time");
        delete_option("ilab_media_tools_{$batch}_last_update");
    }

    /**
     * Adds posts to a batch and runs it.  If another one of this batch type is running, it will be cancelled.
     * @param $batch
     * @param $postIDs
     * @throws \Exception
     */
    public function addToBatchAndRun($batch, $postIDs) {
        if (!isset(static::$batchClasses[$batch])) {
            throw new \Exception("Batch '$batch' is not registered.");
        }

        $this->reset($batch);

        if (count($postIDs) == 0) {
            return;
        }

        $testResult = $this->testConnectivity();
        if ($testResult !== true) {
            $error = 'Unknown';

            if (is_wp_error($testResult)) {
                $error = $testResult->get_error_message();
            } else if (is_array($testResult)) {
                $error = 'HTTP response code was '.$testResult['response']['code'];
            }

            $storageSettingsURL = admin_url('admin.php?page=media-tools-s3#ilab-media-s3-batch-settings');
            $message = "There was an error attempting to run your batch.  Try changing the <strong>Connection Timeout</strong> in <a href='$storageSettingsURL'>Storage Settings</a> to a higher number like 0.1 or 1 to see if that helps.  The actual error was: $error";
            $this->setErrorMessage($batch, $message);

            throw new \Exception($message);
        }

        $firstPostFile = get_attached_file($postIDs[0]);
        $fileName = basename($firstPostFile);

        $this->setCurrent($batch, 1);
        $this->setTotalCount($batch, count($postIDs));
        $this->setCurrentFile($batch, $fileName);
        $this->setShouldCancel($batch, false);
        $this->setStatus($batch, true);

        /** @var BackgroundProcess $batchProcess */
        $batchProcess = new static::$batchClasses[$batch]();

        $index = 0;
        foreach($postIDs as $postID) {
            $batchProcess->push_to_queue(['index' => $index, 'post' => $postID]);
            $index++;
        }

        $batchProcess->save();
        $batchProcess->dispatch();

        $this->setLastRun($batch, time());
    }

    /**
     * Determines if enough time has elapsed since the last time the batch was "forced" to run and then runs it if so
     */
    public function dispatchBatchesIfNeeded() {
        foreach(static::$batchClasses as $batch => $batchClass) {
            if ($this->status($batch)) {
                $lastRun = $this->lastRun($batch);

                if ((time() - $lastRun) > 60) {
                    $this->setLastRun($batch, time());

                    /** @var BackgroundProcess $process */
                    $process = new $batchClass();
                    $process->dispatch();
                }
            }
        }
    }

    /**
     * Tests connectivity to for the bulk importer
     * @param ErrorCollector|null $errorCollector
     * @return array|bool|\WP_Error
     */
    public function testConnectivity($errorCollector = null) {
        $url = add_query_arg(['action' => 'ilab_batch_test', 'nonce' => wp_create_nonce('ilab_batch_test')], admin_url('admin-ajax.php'));
        $timeout = EnvironmentOptions::Option('ilab-media-s3-batch-timeout', null, 0.1);
        $args = [
            'timeout'   => $timeout,
            'blocking'  => true,
            'body'      => 'allo mate',
            'cookies'   => $_COOKIE,
            'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
        ];

        $rawUrl = esc_url_raw($url);
        $result = wp_remote_post($rawUrl, $args);

        if (is_wp_error($result)) {
            if ($errorCollector) {
                $errorCollector->addError("Could not connect to the server for bulk background processing.  The error was: ".$result->get_error_message());
            }

            Logger::error("Testing connectivity to the site for background processing failed.  Error was: ".$result->get_error_message());
            return $result;
        } else if ($result['response']['code'] != 200) {
            if ($errorCollector) {
                $errorCollector->addError("Could not connect to the server for bulk background processing.  The server returned a {$result['response']['code']} response code.");
            }

            Logger::error("Testing connectivity to the site for background processing failed.  Site returned a {$result['response']['code']} status.", ['body' => $result['body']]);
            return $result;
        }

        $json = json_decode($result['body'], true);
        if (empty($json) || !isset($json['test']) || (isset($json['test']) && ($json['test'] != 'worked'))) {
            if ($errorCollector) {
                $errorCollector->addError("Was able to connect to the server for bulk background processing but the JSON response was incorrect: ".$result['body']);
            }

            Logger::error("Testing connectivity to the site for background processing failed.  Was able to connect to the site but the JSON response was not expected.", ['body' => $result['body']]);
            return new \WP_Error(500, "The server response from the connectivity test was not in the expected format.");
        }

        return true;
    }

    public function testAccess() {
        json_response(['test' => 'worked']);
    }

}