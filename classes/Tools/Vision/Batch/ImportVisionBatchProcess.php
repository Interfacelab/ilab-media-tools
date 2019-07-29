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


namespace ILAB\MediaCloud\Tools\Vision\Batch;

use ILAB\MediaCloud\Tasks\BackgroundProcess;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Background processing job for processing existing media with AWS Rekognizer
 */
class ImportVisionBatchProcess extends BackgroundProcess {
	protected $action = 'ilab_vision_import_process';

	protected function shouldHandle() {
	    return !BatchManager::instance()->shouldCancel('vision');
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

        BatchManager::instance()->setCurrentID('vision', $post_id);

		BatchManager::instance()->setCurrent('vision', $index + 1);

		$data = wp_get_attachment_metadata($post_id);

		if (empty($data)) {
			Logger::info( 'Missing metadata', $item);
			return false;
		}

		if (!isset($data['s3'])) {
			Logger::info( 'Missing s3 metadata', $item);
			return false;
		}

		$fileName = basename($data['file']);
		BatchManager::instance()->setCurrentFile('vision', $fileName);


		$visionTool = ToolsManager::instance()->tools['vision'];
		$data = $visionTool->processImageMeta($data, $post_id);
		wp_update_attachment_metadata($post_id, $data);


        $endTime = microtime(true) - $startTime;
        BatchManager::instance()->incrementTotalTime('vision', $endTime);

		return false;
	}

	public function dispatch() {
		Logger::info( 'Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		Logger::info( 'Task complete');
		BatchManager::instance()->reset('vision');
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

        BatchManager::instance()->reset('vision');
	}

	public static function cancelAll() {
		Logger::info( 'Cancel all processes');

		wp_clear_scheduled_hook('wp_ilab_vision_import_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_ilab_vision_import_process_batch_%'");
		foreach($res as $batch) {
			Logger::info( "Deleting batch {$batch->option_name}");
			delete_option($batch->option_name);
		}

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like '__wp_ilab_vision_import_process_batch_%'");
		foreach($res as $batch) {
			delete_option($batch->option_name);
		}

        BatchManager::instance()->reset('vision');

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
}
