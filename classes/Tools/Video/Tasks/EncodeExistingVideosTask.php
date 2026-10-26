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


namespace MediaCloud\Plugin\Tools\Video\Tasks;

use MediaCloud\Plugin\Tasks\AttachmentTask;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\VideoEncodingTool;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Tools\Video\Driver\Mux\Tasks\rocket_clean_domain;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

class EncodeExistingVideosTask extends AttachmentTask {

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'encode-existing-videos-task';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Encode Existing Videos';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.encode-existing-videos-task';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Encode Existing Videos';
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
		return null;
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
		return '/batch/encode-existing-videos-task';
	}

	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		$options = [
			'selected-items' => [
				"title" => "Selected Media",
				"description" => "If you want to process just a small subset of items, click on 'Select Media'",
				"type" => "media-select",
				"media-types" => ['video']
			],
			'skip-imported' => [
				"title" => "Skip Imported",
				"description" => "Skip items that have already been imported.",
				"type" => "checkbox",
				"default" => true
			],
			'sort-order' => [
				"title" => "Sort Order",
				"description" => "Controls the order that items from your media library are migrated to cloud storage.",
				"type" => "select",
				"options" => [
					'default' => 'Default',
					'date-asc' => "Oldest first",
					'date-desc' => "Newest first",
					'title-asc' => "Title, A-Z",
					'title-desc' => "Title, Z-A",
					'filename-asc' => "File name, A-Z",
					'filename-desc' => "File name, Z-A",
				],
				"default" => 'default',
			],
		];

		return $options;
	}

	//endregion

	//region Data

	protected function filterPostArgs($args) {
		$args['post_mime_type'] = 'video';

		if (isset($this->options['skip-imported'])) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key'     => '_wp_attachment_metadata',
					'value'   => '"mux"',
					'compare' => 'NOT LIKE',
					'type'    => 'CHAR',
				],
				[
					'key'     => '_wp_attachment_metadata',
					'value'   => '"bunny-stream"',
					'compare' => 'NOT LIKE',
					'type'    => 'CHAR',
				],
			];
		}

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

		$meta = get_post_meta($post_id, '_wp_attachment_metadata', true);
		if (empty($meta)) {
			return true;
		}

		$type = arrayPath($meta, 'type', null);
		if (empty($type)) {
			$type = arrayPath($meta, 'mime_type', null);
			if (empty($type)) {
				$type = arrayPath($meta, 's3/mime-type', null);

				if (empty($type)) {
					Logger::warning("No mime type found for attachment.", [], __METHOD__, __LINE__);
					return true;
				}
			}
		}

		if (strpos($type, 'video') !== 0) {
			Logger::error("The mime type '$type' is not a video.", [], __METHOD__, __LINE__);
			return true;
		}

		/** @var VideoEncodingTool $muxTool */
		$videoEncodingTool = ToolsManager::instance()->tools['video-encoding'];
		$videoEncodingTool->importUrl($post_id, $meta);

		Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

		return true;
	}


	public function complete() {
		do_action('media-cloud/storage/migration/complete');

		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}
	}

	//endregion
}