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
class ILABS3ImportProcess extends ILAB_WP_Background_Process {
	protected $action = 'ilab_s3_import_process';

	public function task($item) {
		$shouldCancel = get_option('ilab_s3_import_should_cancel', false);
		if ($shouldCancel) {
			$this->cancel_process();
			return false;
		}

		$index = $item['index'];
		$post_id = $item['post'];

		$isDocument = false;

		update_option('ilab_s3_import_current', $index+1);

		$data = wp_get_attachment_metadata($post_id);

		if (empty($data)) {
			$isDocument = true;
			$post_mime = get_post_mime_type($post_id);
			$upload_file = get_attached_file($post_id);
			$file = _wp_relative_upload_path($upload_file);

			$fileName = basename($upload_file);

			update_option('ilab_s3_import_current_file', $fileName);

			$data = [ 'file' => $file ];
			if (file_exists($upload_file)) {
				$mime = mime_content_type($upload_file);
				if ($mime == 'image/vnd.adobe.photoshop') {
					$mime = 'application/vnd.adobe.photoshop';
				}

				$data['ilab-mime'] = $mime;
				if ($mime != $post_mime) {
					wp_update_post(['ID'=>$post_id, 'post_mime_type' => $mime]);
				}
				$imagesize = getimagesize( $upload_file );
				if ($imagesize) {
					if (file_is_displayable_image($upload_file)) {
						$data['width'] = $imagesize[0];
						$data['height'] = $imagesize[1];
						$data['sizes']=[
							'full' => [
								'file' => $data['file'],
								'width' => $data['width'],
								'height' => $data['height']
							]
						];

						$isDocument = false;
					}
				}

				if ($mime == 'application/pdf') {
					$renderPDF = apply_filters('ilab_imgix_render_pdf', false);

					set_error_handler(function($errno, $errstr, $errfile, $errline){
						throw new Exception($errstr);
					}, E_RECOVERABLE_ERROR);

					try {
						$parser = new \Smalot\PdfParser\Parser();
						$pdf = $parser->parseFile($upload_file);
						$pages = $pdf->getPages();
						if (count($pages)>0) {
							$page = $pages[0];
							$details = $page->getDetails();
							if (isset($details['MediaBox'])) {
								$data['width'] = $details['MediaBox'][2];
								$data['height'] = $details['MediaBox'][3];

								if ($renderPDF) {
									$data['sizes']=[
										'full' => [
											'file' => $data['file'],
											'width' => $data['width'],
											'height' => $data['height']
										]
									];

									$isDocument = false;
								}
							}
						}
					} catch (Exception $ex) {
						error_log("PDF Exception: ".$ex->getMessage());
					}

					restore_error_handler();
				}
			}
		} else {
			$fileName = basename($data['file']);
			update_option('ilab_s3_import_current_file', $fileName);
		}

		$s3tool = ILabMediaToolsManager::instance()->tools['s3'];
		$data = $s3tool->updateAttachmentMetadata($data, $post_id);

		if ($isDocument) {
			update_post_meta($post_id, 'ilab_s3_info', $data);
		} else {
			wp_update_attachment_metadata($post_id, $data);
		}

		return false;
	}

	public function dispatch() {
		parent::dispatch();
	}

	protected function complete() {
		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
		parent::complete();
	}

	public function cancel_process() {
		parent::cancel_process();

		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
	}

	public static function cancelAll() {
		wp_clear_scheduled_hook('wp_ilab_s3_import_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_ilab_s3_import_process_batch_%'");
		foreach($res as $batch) {
			delete_option($batch->option_name);
		}

		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
	}
}
