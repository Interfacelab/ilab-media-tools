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

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\TransferStats;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use function ILAB\MediaCloud\Utilities\json_response;
use Psr\Http\Message\ResponseInterface;

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
	 * Sets the current thumbnail of the file being processed
	 * @param $batch
	 * @param array $thumbUrl
	 */
	public function setCommandLineThumb($batch, $thumbUrl) {
		update_option("ilab_media_tools_{$batch}_command_line_thumb", $thumbUrl);
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
		$this->setLastUpdateToNow($batch);
		update_option("ilab_media_tools_{$batch}_file", $file);
	}


	/**
	 * Sets the time the last task update occurred
	 * @param $batch
	 */
	public function setLastUpdateToNow($batch) {
		update_option("ilab_media_tools_{$batch}_last_update", microtime(true));
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

	/**
	 * Returns the amount of time that has elapsed since the last item was processed.
	 * @param $batch
	 * @return float
	 */
	public function lastCommandLineThumb($batch) {
		return get_option("ilab_media_tools_{$batch}_command_line_thumb", false);
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
	        $commandLine = Environment::Option("mcloud-{$batch}-batch-command-line-processing", null, false);

	        $found = false;
	        if ($commandLine) {
	        	$thumbData = $this->lastCommandLineThumb($batch);
	        	if (!empty($thumbData)) {
	        		$thumbUrl = $thumbData['thumbUrl'];
	        		$icon = $thumbData['icon'];

	        		$found = true;
		        }
	        }

	        if (!$found) {
		        $thumb = wp_get_attachment_image_src($this->currentID($batch), 'thumbnail', true);
		        if (!empty($thumb)) {
			        $thumbUrl = $thumb[0];
			        $icon = (($thumb[1] != 150) && ($thumb[2] != 150));
		        }
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
	    Environment::DeleteOption('mcloud-storage-batch-command-line-processing');

        delete_option("ilab_media_tools_{$batch}_status");
        delete_option("ilab_media_tools_{$batch}_current");
        delete_option("ilab_media_tools_{$batch}_file");
        delete_option("ilab_media_tools_{$batch}_total");
        delete_option("ilab_media_tools_{$batch}_last_run");
        delete_option("ilab_media_tools_{$batch}_total_time");
        delete_option("ilab_media_tools_{$batch}_last_time");
	    delete_option("ilab_media_tools_{$batch}_last_update");
	    delete_option("ilab_media_tools_{$batch}_command_line_thumb");
    }

	/**
	 * Adds posts to a batch.  If another one of this batch type is running, it will be cancelled.
	 * @param $batch
	 * @param $postIDs
	 */
	public function addToBatch($batch, $postIDs) {

	}

    /**
     * Adds posts to a batch and runs it.  If another one of this batch type is running, it will be cancelled.
     * @param string $batch
     * @param array $postIDs
     * @param array|null $options
     * @throws \Exception
     */
    public function addToBatchAndRun($batch, $postIDs, $options = null) {
        if (!isset(static::$batchClasses[$batch])) {
            throw new \Exception("Batch '$batch' is not registered.");
        }

        $this->reset($batch);

        if (count($postIDs) == 0) {
            return;
        }

        Logger::info("Testing connectivity.");
        $testResult = $this->testConnectivity(null, 3);
	    Logger::info("Finished Testing connectivity.");
        if ($testResult !== true) {
            $error = 'Unknown';

            if ($testResult instanceof \Exception) {
                $error = $testResult->getMessage();
            } else {
                $error = 'HTTP response code was '.$testResult->getStatusCode();
            }

            $storageSettingsURL = admin_url('admin.php?page=media-cloud-settings&tab=batch-processing');
            if (strpos($error, 'SSL') !== false) {
	            $hint = "Try changing the <strong>Verify SSL</strong> in <a href='$storageSettingsURL'>Batch Processing Settings</a> to <strong>No</strong> to see if that helps.";
            } else {
	            $hint = "Try changing the <strong>Connection Timeout</strong> in <a href='$storageSettingsURL'>Batch Processing Settings</a> to a higher number like 0.1 or 1 to see if that helps.";
            }
            $message = "There was an error attempting to run your batch.  $hint  The actual error was: $error";
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
        	$details = ['index' => $index, 'post' => $postID, 'options' => (empty($options)) ? [] : $options];
            $batchProcess->push_to_queue($details);
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
	 * Posts a background processing request
	 *
	 * @param $url
	 * @param $args
	 *
	 * @return bool|\Exception|ResponseInterface
	 */
    public static function postRequest($url, $args, $timeoutOverride = false) {
	    $verifySSL = Environment::Option('mcloud-storage-batch-verify-ssl', null, 'default');
	    $connectTimeout = Environment::Option('mcloud-storage-batch-connect-timeout', null, 0);
	    $timeout = Environment::Option('mcloud-storage-batch-timeout', null, 0.1);
	    $skipDNS = Environment::Option('mcloud-storage-batch-skip-dns', null, false);

	    if ($skipDNS) {
		    $ip = getHostByName(getHostName());
		    if (empty($ip)) {
			    $ip = $_SERVER['SERVER_ADDR'];
		    }

		    if (empty($ip)) {
			    $ip = '127.0.0.1';
		    }

		    $host = parse_url($url, PHP_URL_HOST);
		    $url = str_replace($host, $ip, $url);

		    $headers = [
			    'Host' => $host
		    ];

		    if (isset($args['headers'])) {
			    $args['headers'] = array_merge($args['headers'], $headers);
		    } else {
			    $args['headers'] = $headers;
		    }

		    $cookies = CookieJar::fromArray($args['cookies'], $ip);
	    } else {
		    $cookies = CookieJar::fromArray($args['cookies'], $_SERVER['HTTP_HOST']);
	    }

	    if ($verifySSL == 'default') {
		    $verifySSL = apply_filters( 'https_local_ssl_verify', true);
	    } else {
		    $verifySSL = ($verifySSL == 'yes');
	    }

	    $rawUrl = esc_url_raw( $url );

	    $options = [
		    'synchronous' => !empty($args['blocking']),
		    'cookies' => $cookies,
		    'verify' => $verifySSL
	    ];

	    if (isset($args['body'])) {
		    if (is_array($args['body'])) {
			    $options['form_params'] = $args['body'];
		    } else {
			    $options['body'] = $args['body'];
		    }
	    }

	    if (!empty($headers)) {
		    $options['headers'] = $headers;
	    }

	    if (!empty($connectTimeout)) {
		    $options['connect_timeout'] = $connectTimeout;
	    }


	    if ($timeoutOverride !== false) {
		    $options['timeout'] = $timeoutOverride;
	    } else {
		    $options['timeout'] = max(0.01, $timeout);
	    }

	    $client = new Client();
	    try {
		    if (empty($options['synchronous'])) {
			    Logger::info("Async call to $rawUrl");
			    $options['synchronous'] = true;
			    $client->postSync($rawUrl, $options);
			    Logger::info("Async call to $rawUrl complete.");
			    return true;
		    } else {
			    Logger::info("Synchronous call to $rawUrl");
			    $result = $client->post($rawUrl, $options);
			    Logger::info("Synchronous call to $rawUrl complete.");
			    return $result;
		    }
	    } catch (\Exception $ex) {
		    if (!empty($args['blocking'])) {
			    return $ex;
		    } else {
			    Logger::info("Async exception: ".$ex->getMessage());
		    }
	    }

	    return true;
    }

    /**
     * Tests connectivity to for the bulk importer
     * @param ErrorCollector|null $errorCollector
	 * @return bool|ResponseInterface|\Exception|\WP_Error
	 */
    public function testConnectivity($errorCollector = null, $timeoutOverride = false) {
        $url = add_query_arg(['action' => 'ilab_batch_test', 'nonce' => wp_create_nonce('ilab_batch_test')], admin_url('admin-ajax.php'));
        $args = [
            'blocking'  => true,
            'body'      => 'allo mate',
            'cookies'   => $_COOKIE,
        ];

        /** @var bool|ResponseInterface $result */
        $result = static::postRequest($url, $args, $timeoutOverride);

        if ($result === true) {
        	return true;
        }

        if ($result instanceof \Exception) {
        	if ($errorCollector) {
        		$errorCollector->addError("Could not connect to the server for bulk background processing.  The error was: ".$result->getMessage());
	        }

	        Logger::error("Testing connectivity to the site for background processing failed.  Error was: ".$result->getMessage());
	        return $result;
        } else if ($result->getStatusCode() != 200) {
            if ($errorCollector) {
                $errorCollector->addError("Could not connect to the server for bulk background processing.  The server returned a {$result->getStatusCode()} response code.");
            }

            Logger::error("Testing connectivity to the site for background processing failed.  Site returned a {$result->getStatusCode()} status.", ['body' => $result->getBody()]);
            return $result;
        }

        $json = json_decode((string)$result->getBody(), true);
        if (empty($json) || !isset($json['test']) || (isset($json['test']) && ($json['test'] != 'worked'))) {
            if ($errorCollector) {
                $errorCollector->addError("Was able to connect to the server for bulk background processing but the JSON response was incorrect: ".$result->getBody());
            }

            Logger::error("Testing connectivity to the site for background processing failed.  Was able to connect to the site but the JSON response was not expected.", ['body' => $result->getBody()]);
            return new \WP_Error(500, "The server response from the connectivity test was not in the expected format.");
        }

        return true;
    }

    public function testAccess() {
        json_response(['test' => 'worked']);
    }

}