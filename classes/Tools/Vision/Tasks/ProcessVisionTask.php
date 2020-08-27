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

namespace MediaCloud\Plugin\Tools\Vision\Tasks;

use MediaCloud\Plugin\Tasks\AttachmentTask;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Vision\VisionTool;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\postIdExists;

class ProcessVisionTask extends AttachmentTask {

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'process-vision';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Process Image With Vision';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.process-vision';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Process With Vision';
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
		return "Process With Vision";
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/vision';
	}

	/**
	 * Determines if a task is a user facing task.
	 * @return bool|false
	 */
	public static function userTask() {
		return true;
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
				"type" => "media-select"
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
			]
		];




		return $options;
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
		add_filter('media-cloud/vision/allow-background-processing', '__return_false');

		$post_id = $item['id'];
		if (!postIdExists($post_id)) {
			return true;
		}

		$this->updateCurrentPost($post_id);


		Logger::info("Processing $post_id", [], __METHOD__, __LINE__);

		/** @var VisionTool $visionTool */
		$visionTool = ToolsManager::instance()->tools['vision'];
		$data = wp_get_attachment_metadata($post_id);
		$data = $visionTool->processImageMeta($data, $post_id);
		update_post_meta($post_id, '_wp_attachment_metadata', $data);

		Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

		return true;
	}

	//endregion
}