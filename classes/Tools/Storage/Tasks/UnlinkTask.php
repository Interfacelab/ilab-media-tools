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

namespace MediaCloud\Plugin\Tools\Storage\Tasks;

use MediaCloud\Plugin\Tasks\AttachmentTask;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\postIdExists;

class UnlinkTask extends AttachmentTask {
	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'unlink-media-task';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Unlink From Cloud Storage';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.unlink-media-task';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Unlink From Cloud';
	}

	/**
	 * Controls if this task stops on an error.
	 *
	 * @return bool
	 */
	public static function stopOnError() {
		return false;
	}

	/**
	 * Bulk action title.
	 *
	 * @return string|null
	 */
	public static function bulkActionTitle() {
		return "Unlink from Cloud Storage";
	}

	/**
	 * Determines if a task is a user facing task.
	 * @return bool|false
	 */
	public static function userTask() {
		return true;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/unlink';
	}

	public static function warnOption() {
		return 'unlink-task-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'I UNDERSTAND';
	}

	public static function warnConfirmationText() {
		return "It is important that you backup your database prior to running this unlink task.  To continue, please type 'I UNDERSTAND' to confirm that you have backed up your database.";
	}


	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [
			'selected-items' => [
				"title" => "Selected Media",
				"description" => "If you want to process just a small subset of items, click on 'Select Media'",
				"type" => "media-select"
			]
		];
	}

	public function reporter() {
		if (empty($this->reportHeaders)) {
			$allSizes = ilab_get_image_sizes();
			$sizeKeys = array_keys($allSizes);
			sort($sizeKeys);

			$this->reportHeaders = [
				'Post ID',
				'Attachment URL',
				'Local URL',
				'Status'
			];
		}

		return parent::reporter();
	}

	//endregion

	//region Data

	protected function filterPostArgs($args) {
		$args['meta_query'] = [
			'relation' => 'OR',
			[
				'key'     => '_wp_attachment_metadata',
				'value'   => '"s3"',
				'compare' => 'LIKE',
				'type'    => 'CHAR',
			],
			[
				'key'     => 'ilab_s3_info',
				'compare' => 'EXISTS',
			],
		];

		return $args;
	}

	//endregion

	//region Execution

	/**
	 * Performs the actual task
	 *
	 * @param $item
	 *
	 * @return bool|void
	 * @throws \Exception
	 */
	public function performTask($item) {
		$post_id = $item['id'];
		if (!postIdExists($post_id)) {
			return true;
		}

		$this->updateCurrentPost($post_id);

		Logger::info("Processing $post_id", [], __METHOD__, __LINE__);

		$isS3Info = true;
		$meta = get_post_meta($post_id, 'ilab_s3_info', true);
		if (empty($meta)) {
			$isS3Info = false;
			$meta = get_post_meta($post_id, '_wp_attachment_metadata', true);
		}

		$changed = false;
		$url = wp_get_attachment_url($post_id);

		if (empty($meta)) {
			$this->reporter()->add([
				$post_id,
				$url,
				null,
				'Missing metadata'
			]);

			Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

			return true;
		}


		if (isset($meta['s3'])) {
			$changed = true;
			unset($meta['s3']);
		}

		if(isset($meta['sizes'])) {
			$sizes = $meta['sizes'];
			foreach($sizes as $size => $sizeData) {
				if(isset($sizeData['s3'])) {
					$changed = true;
					unset($sizeData['s3']);
				}

				$sizes[$size] = $sizeData;
			}

			$meta['sizes'] = $sizes;
		}

		if (isset($meta['original_image_s3'])) {
			$changed = true;
			unset($meta['original_image_s3']);
		}

		if ($changed) {
			if ($isS3Info) {
				delete_post_meta($post_id, 'ilab_s3_info');
			} else {
				update_post_meta($post_id, '_wp_attachment_metadata', $meta);
			}

			$newUrl = wp_get_attachment_url($post_id);

			$this->reporter()->add([
				$post_id,
				$url,
				$newUrl,
				'Unlinked successfully'
			]);
		} else {
			$this->reporter()->add([
				$post_id,
				$url,
				null,
				'Missing cloud storage metadata'
			]);
		}

		Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

		return true;
	}

	public function complete() {
		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}
	}

	//endregion
}
