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
use ILAB\MediaCloud\Tools\Storage\ImportProgressDelegate;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILABS3ImportProcess
 *
 * Background processing job for importing existing media to S3
 */
class MigrateToStorageBatchProcess extends BackgroundProcess implements ImportProgressDelegate {
	#region Variables
	protected $action = 'ilab_s3_import_process';
	#endregion

	#region Task Related
	protected function shouldHandle() {
	    return !BatchManager::instance()->shouldCancel('storage');
	}

	public function task($item) {
	    $startTime = microtime(true);


		//Logger::info( 'Start Task', $item);
		if (!$this->shouldHandle()) {
			Logger::info( 'Task cancelled', $item);
			return false;
		}

		$index = $item['index'];
		$post_id = $item['post'];
		$options = (isset($item['options'])) ? $item['options'] : [];

		BatchManager::instance()->setCurrentID('storage', $post_id);
		BatchManager::instance()->setCurrent('storage', $index + 1);

		/** @var StorageTool $s3tool */
		$s3tool = ToolsManager::instance()->tools['storage'];
		$s3tool->processImport($index, $post_id, $this, $options);

        $endTime = microtime(true) - $startTime;

        BatchManager::instance()->incrementTotalTime('storage', $endTime);

		return false;
	}

	public function dispatch() {
		Logger::info( 'Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		Logger::info( 'Task complete');
		BatchManager::instance()->reset('storage');
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

        BatchManager::instance()->reset('storage');
	}

	public static function cancelAll() {
		Logger::info( 'Cancel all processes');

		wp_clear_scheduled_hook('wp_ilab_s3_import_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_ilab_s3_import_process_batch_%'");
		foreach($res as $batch) {
			Logger::info( "Deleting batch {$batch->option_name}");
			delete_option($batch->option_name);
		}

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like '__wp_ilab_s3_import_process_batch_%'");
		foreach($res as $batch) {
			delete_option($batch->option_name);
		}

        BatchManager::instance()->reset('storage');

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
	#endregion

	#region ImportProgressDelegate
	public function updateCurrentIndex($newIndex) {
        BatchManager::instance()->setCurrent('storage', $newIndex);
	}

	public function updateCurrentFileName($newFile) {
        BatchManager::instance()->setCurrentFile('storage', $newFile);
	}
	#endregion
}
