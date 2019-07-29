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

namespace ILAB\MediaCloud\Tools\Debugging\System\Batch;

use ILAB\MediaCloud\Tasks\BackgroundProcess;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class TestBatchProcess
 * @package ILAB\MediaCloud\Tools\Debugging\System\Batch
 */
class TestBatchProcess extends BackgroundProcess {
	protected $action = 'mcloud_system_test_process';

	protected function shouldHandle() {
		return !BatchManager::instance()->shouldCancel('system-testing');
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

		BatchManager::instance()->setLastUpdateToNow('system-testing');
		BatchManager::instance()->setCurrentID('system-testing', $post_id);
		BatchManager::instance()->setCurrent('system-testing', $index + 1);

		sleep(1);

		$endTime = microtime(true) - $startTime;
		BatchManager::instance()->incrementTotalTime('system-testing', $endTime);

		return false;
	}

	protected function complete() {
		Logger::info( 'Task complete');
		BatchManager::instance()->reset('system-testing');
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

		BatchManager::instance()->reset('system-testing');
	}

	public static function cancelAll() {
		Logger::info( 'Cancel all processes');

		wp_clear_scheduled_hook('wp_mcloud_system_test_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_mcloud_system_test_process_batch_%'");
		foreach($res as $batch) {
			Logger::info( "Deleting batch {$batch->option_name}");
			delete_option($batch->option_name);
		}

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like '__wp_mcloud_system_test_process_batch_%'");
		foreach($res as $batch) {
			delete_option($batch->option_name);
		}

		BatchManager::instance()->reset('system-testing');

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
}
