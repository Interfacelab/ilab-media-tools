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

use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logger;
use Smalot\PdfParser\Parser;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class RegenerateThumbnailsProcess
 *
 * Background processing job for regenerating thumbnails
 */
class RegenerateThumbnailsProcess extends BackgroundProcess {
	protected $action = 'ilab_cloud_regenerate_process';

	protected function shouldHandle() {
		return !get_option('ilab_cloud_regenerate_should_cancel', false);
	}

	public function task($item) {
		Logger::info( 'Start Task', $item);
		if (!$this->shouldHandle()) {
			Logger::info( 'Task cancelled', $item);
			return false;
		}

		$index = $item['index'];
		$post_id = $item['post'];

		$fileName = get_attached_file( $post_id);

		if ($fileName) {
			$fileparts = explode('/', $fileName);
			$fileName = array_pop($fileparts);

			update_option('ilab_cloud_regenerate_current_file', $fileName);
		}

		update_option('ilab_cloud_regenerate_current', $index+1);

		$storageTool = ToolsManager::instance()->tools['storage'];
		$storageTool->regenerateFile($post_id);

		return false;
	}

	public function dispatch() {
		Logger::info( 'Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		Logger::info( 'Task complete');
		delete_option('ilab_cloud_regenerate_status');
		delete_option('ilab_cloud_regenerate_total_count');
		delete_option('ilab_cloud_regenerate_current');
		delete_option('ilab_cloud_regenerate_current_file');
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

		delete_option('ilab_cloud_regenerate_status');
		delete_option('ilab_cloud_regenerate_total_count');
		delete_option('ilab_cloud_regenerate_current');
		delete_option('ilab_cloud_regenerate_current_file');
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

		delete_option('ilab_cloud_regenerate_status');
		delete_option('ilab_cloud_regenerate_total_count');
		delete_option('ilab_cloud_regenerate_current');
		delete_option('ilab_cloud_regenerate_current_file');

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
}
