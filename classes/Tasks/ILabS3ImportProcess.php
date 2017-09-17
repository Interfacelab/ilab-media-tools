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

use ILAB\MediaCloud\ILabMediaToolsManager;
use ILAB\MediaCloud\Utilities\ILabMediaToolLogger;
use Smalot\PdfParser\Parser;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILABS3ImportProcess
 *
 * Background processing job for importing existing media to S3
 */
class ILabS3ImportProcess extends ILabWPBackgroundProcess {
	protected $action = 'ilab_s3_import_process';

	protected function shouldHandle() {
		return !get_option('ilab_s3_import_should_cancel', false);
	}

	public function task($item) {
		ILabMediaToolLogger::info('Start Task', $item);
		if (!$this->shouldHandle()) {
			ILabMediaToolLogger::info('Task cancelled', $item);
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

			ILabMediaToolLogger::info('Task metadata was empty.', $item);

			if (file_exists($upload_file)) {
				$mime = null;

				$ftype = wp_check_filetype($upload_file);
				if (!empty($ftype) && isset($ftype['type'])) {
					$mime  = $ftype['type'];
				}

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
						throw new \Exception($errstr);
					}, E_RECOVERABLE_ERROR);

					try {
						$parser = new Parser();
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
					} catch (\Exception $ex) {
						ILabMediaToolLogger::error('PDF Exception.', array_merge($item, ['exception'=>$ex->getMessage()]));
					}

					restore_error_handler();
				}
			}
		} else {
			$fileName = basename($data['file']);
			update_option('ilab_s3_import_current_file', $fileName);
			ILabMediaToolLogger::info('Task metadata was not empty.', $item);
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
		ILabMediaToolLogger::info('Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		ILabMediaToolLogger::info('Task complete');
		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
		parent::complete();
	}

	public function cancel_process() {
		ILabMediaToolLogger::info('Cancel process');

		parent::cancel_process();

		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');
	}

	public static function cancelAll() {
		ILabMediaToolLogger::info('Cancel all processes');

		wp_clear_scheduled_hook('wp_ilab_s3_import_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_ilab_s3_import_process_batch_%'");
		foreach($res as $batch) {
			ILabMediaToolLogger::info("Deleting batch {$batch->option_name}");
			delete_option($batch->option_name);
		}

		delete_option('ilab_s3_import_status');
		delete_option('ilab_s3_import_total_count');
		delete_option('ilab_s3_import_current');
		delete_option('ilab_s3_import_current_file');

		ILabMediaToolLogger::info("Current cron", get_option('cron', []));
		ILabMediaToolLogger::info('End cancel all processes');
	}
}
