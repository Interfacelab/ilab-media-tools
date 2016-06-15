<?php

if (!defined('ABSPATH')) { header('Location: /'); die; }

require_once('wp-background-process.php');

require_once(ILAB_CLASSES_DIR.'/ilab-media-tools-manager.php');
require_once(ILAB_CLASSES_DIR.'/tools/s3/ilab-media-s3-tool.php');

class ILABS3ImportProcess extends WP_Background_Process {
	protected $action = 'ilab_s3_import_process';

	protected function task($item) {
		$index = $item['index'];
		$post_id = $item['post'];

		update_option('ilab_s3_import_current', $index+1);

		$data = wp_get_attachment_metadata($post_id);

		$s3tool = ILabMediaToolsManager::instance()->tools['s3'];
		$s3tool->updateAttachmentMetadata($data, $post_id);
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