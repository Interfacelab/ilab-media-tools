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
use MediaCloud\Plugin\Tools\Storage\StorageUtilities;
use MediaCloud\Plugin\Utilities\Logging\Logger;

class FixMetadataTask extends AttachmentTask {

	//region Static Task Properties

	/**
	 * Report headers
	 *
	 * @var array
	 */
	protected $reportHeaders = [
		'Post ID',
		'Status'
	];

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'fix-metadata';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Fix Cloud Metadata';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Fix Cloud Metadata';
	}

	public static function showInMenu() {
		return true;
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
		return 'Fix Metadata';
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
		return '/batch/fix-metadata';
	}

	public static function warnOption() {
		return 'fix-metadata-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'I UNDERSTAND';
	}

	public static function warnConfirmationText() {
		return "It is important that you backup your database prior to running this import task.  To continue, please type 'I UNDERSTAND' to confirm that you have backed up your database.";
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.fix-metadata';
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
			],
			'skip-imported' => [
				"title" => "Skip Imported",
				"description" => "Skip items that have already been imported.",
				"type" => "checkbox",
				"default" => false
			],
		];
	}

	/** @var null|\Closure  */
	public static $callback = null;

	//endregion

	//region Data

	protected function filterPostArgs($args) {
		if (isset($this->options['skip-imported'])) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'relation' => 'AND',
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"s3"',
						'compare' => 'NOT LIKE',
						'type'    => 'CHAR',
					],
					[
						'key'     => 'ilab_s3_info',
						'compare' => 'NOT EXISTS',
					],
				],
				[
					'relation' => 'AND',
					[
						'key'     => '_wp_attachment_metadata',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'ilab_s3_info',
						'compare' => 'NOT EXISTS',
					],
				]
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

		Logger::info("Fixing $post_id", [], __METHOD__, __LINE__);

		$fixed = StorageUtilities::instance()->fixMetadata($post_id);
		$this->updateCurrentPost($post_id);

		$this->reporter()->add([
			$post_id,
			($fixed) ? 'Fixed' : 'Skipped'
		]);

		Logger::info("Finished fixing $post_id", [], __METHOD__, __LINE__);

		return true;
	}

	//endregion
}
