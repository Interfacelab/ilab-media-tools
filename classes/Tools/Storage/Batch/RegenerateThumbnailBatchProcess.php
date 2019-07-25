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

namespace ILAB\MediaCloud\Tools\Storage\Batch;

use ILAB\MediaCloud\Tasks\BackgroundProcess;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class RegenerateThumbnailsProcess
 *
 * Background processing job for regenerating thumbnails
 */
class RegenerateThumbnailBatchProcess extends BackgroundProcess {
	protected $action = 'ilab_cloud_regenerate_process';

	protected function shouldHandle() {
	    return !BatchManager::instance()->shouldCancel('thumbnails');
	}

	public function task($item) {
        $startTime = microtime(true);

		Logger::info( 'Start Task', $item);
		if (!$this->shouldHandle()) {
			Logger::info( 'Task cancelled', $item);
			return false;
		}

		$index = $item['index'];
		$post_id = $item['post'];

        BatchManager::instance()->setCurrentID('thumbnails', $post_id);

		$fileName = get_attached_file( $post_id);

		if ($fileName) {
			$fileparts = explode('/', $fileName);
			$fileName = array_pop($fileparts);

			BatchManager::instance()->setCurrentFile('thumbnails', $fileName);
		}


		BatchManager::instance()->setCurrent('thumbnails', $index + 1);

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		$storageTool->regenerateFile($post_id);

        $endTime = microtime(true) - $startTime;
        BatchManager::instance()->incrementTotalTime('thumbnails', $endTime);

		return false;
	}

	public function dispatch() {
		Logger::info( 'Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		Logger::info( 'Task complete');
		BatchManager::instance()->reset('thumbnails');
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

        BatchManager::instance()->reset('thumbnails');
	}

	public static function cancelAll() {
		Logger::info( 'Cancel all processes');

		wp_clear_scheduled_hook('wp_ilab_cloud_regenerate_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_ilab_cloud_regenerate_process_batch_%'");
		foreach($res as $batch) {
			Logger::info( "Deleting batch {$batch->option_name}");
			delete_option($batch->option_name);
		}

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like '__wp_ilab_cloud_regenerate_process_batch_%'");
		foreach($res as $batch) {
			delete_option($batch->option_name);
		}

        BatchManager::instance()->reset('thumbnails');

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
}
