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

use ILAB\MediaCloud\Tools\Storage\ImportProgressDelegate;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logger;
use Smalot\PdfParser\Parser;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILABS3ImportProcess
 *
 * Background processing job for importing existing media to S3
 */
class StorageImportProcess extends BackgroundProcess implements ImportProgressDelegate {
	#region Variables
	protected $action = 'ilab_s3_import_process';
	#endregion

	#region Task Related
	protected function shouldHandle() {
		return !get_option('ilab_s3_import_should_cancel', false);
	}

	public function task($item) {
		Logger::info( 'Start Task', $item);
		if (!$this->shouldHandle()) {
			Logger::info( 'Task cancelled', $item);
			return false;
		}

		$index = $item['index'];
		$post_id = $item['post'];

		$s3tool = ToolsManager::instance()->tools['storage'];
		$s3tool->processImport($index, $post_id, $this);

		return false;
	}

	public function dispatch() {
		Logger::info( 'Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		Logger::info( 'Task complete');
		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
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

		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
	#endregion

	#region ImportProgressDelegate
	public function updateCurrentIndex($newIndex) {
		update_option('ilab_s3_import_current', $newIndex);
	}

	public function updateCurrentFileName($newFile) {
		update_option('ilab_s3_import_current_file', $newFile);
	}
	#endregion
}
