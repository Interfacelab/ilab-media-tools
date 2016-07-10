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

if (!defined('ABSPATH')) { header('Location: /'); die; }

require_once('wp-background-process.php');

require_once(ILAB_CLASSES_DIR.'/ilab-media-tools-manager.php');
require_once(ILAB_CLASSES_DIR.'/tools/s3/ilab-media-s3-tool.php');

/**
 * Class ILABS3ImportProcess
 *
 * Background processing job for importing existing media to S3
 */
class ILABS3ImportProcess extends WP_Background_Process {
	protected $action = 'ilab_s3_import_process';

	protected function task($item) {
		$index = $item['index'];
		$post_id = $item['post'];

		update_option('ilab_s3_import_current', $index+1);

		$data = wp_get_attachment_metadata($post_id);

		$s3tool = ILabMediaToolsManager::instance()->tools['s3'];
		$data = $s3tool->updateAttachmentMetadata($data, $post_id);
		wp_update_attachment_metadata($post_id, $data);

		return false;
	}

	public function dispatch() {
		parent::dispatch();
	}

	protected function complete() {
		update_option('ilab_s3_import_status', false);
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		parent::complete();
	}
}