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
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

class VerifyLibraryTask extends AttachmentTask {

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'verify-library';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Verify Library';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Verify Library';
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
		return '/batch/verify-library';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.verify-library';
	}

	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [
			'include-local' => [
				"title" => "Include Local Files",
				"description" => "Processes all files, including those not on cloud storage.",
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
		if (empty($this->options['include-local'])) {
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
		}

		return $args;
	}

	public function reporter() {
		if (empty($this->reportHeaders)) {
			$allSizes = ilab_get_image_sizes();
			$sizeKeys = array_keys($allSizes);
			sort($sizeKeys);

			$fields = [
				'Post ID',
				'Mime Type',
				'S3 Metadata Status',
				'Attachment URL',
				'Original Source Image URL',
			];

			if (!empty($this->options['include-local'])) {
				$fields = [
					'Post ID',
					'Mime Type',
					'S3 Metadata Status',
					'Attachment URL Local',
					'Attachment URL',
					'Original Source Image URL Local',
					'Original Source Image URL',
				];

				$newSizeKeys = [];
				foreach($sizeKeys as $sizeKey) {
					$newSizeKeys[] = $sizeKey . " Local";
					$newSizeKeys[] = $sizeKey;
				}

				$sizeKeys = $newSizeKeys;
			}

			$this->reportHeaders = array_merge(array_merge($fields, $sizeKeys), ['Notes']);
		}

		return parent::reporter();
	}

	//endregion

	//region Execution

	public function willStart() {
		add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true');
		parent::willStart();
	}

	public function didFinish() {
		remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true');
		parent::didFinish();
	}

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

		Logger::info("Verifying $post_id", [], __METHOD__, __LINE__);

		$this->updateCurrentPost($post_id);

		$callback = !empty(static::$callback) ? static::$callback : function($message, $newLine = false) {};
		ToolsManager::instance()->tools['storage']->verifyPost($post_id, !empty($this->options['include-local']), $this->reporter(), $callback);

		Logger::info("Finished verifying $post_id", [], __METHOD__, __LINE__);

		return true;
	}

	//endregion
}
