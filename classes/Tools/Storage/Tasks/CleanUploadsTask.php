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
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

class CleanUploadsTask extends AttachmentTask {
	protected $reportHeaders = [
		'Post ID',
		'File',
		'Status'
	];

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'clean-uploads';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Clean Uploads';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Clean Uploads';
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
		return "Clean Uploads";
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
		return '/batch/clean-uploads';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.clean-uploads';
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

	public static function warnOption() {
		return 'clean-uploads-task-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'DELETE MY FILES';
	}

	public static function warnConfirmationText() {
		return "This task will delete your media files!  This is not something you can undo, so make sure this is really what you want to do.  To continue, please type 'DELETE MY FILES' to confirm.";
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

	public function prepare($options = [], $selectedItems = []) {
		if (!parent::prepare($options, $selectedItems)) {
			return false;
		}

		$this->addItem(['id' => -1]);
		return true;
	}

	protected function cleanEmptyDirectories() {
		if (defined('UPLOADS')) {
			$root = trailingslashit(ABSPATH).UPLOADS;
		} else {
			$root = trailingslashit(WP_CONTENT_DIR).'uploads';
		}

		$root = trailingslashit($root);

		$folders = [];

		$rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
		foreach($rii as $file) {
			if ($file->isDir()) {
				$path = trailingslashit($file->getPath());
				if (!in_array($path, $folders) && ($path != $root)) {
					$folders[] = $path;
				}
			}
		}

		usort($folders, function($a, $b) {
			$countA = count(explode(DIRECTORY_SEPARATOR, $a));
			$countB = count(explode(DIRECTORY_SEPARATOR, $b));
			if ($countA > $countB) {
				return -1;
			} else if ($countA == $countB) {
				return 0;
			}

			return 1;
		});

		foreach($folders as $folder) {
			$filecount = count(scandir($folder));
			if ($filecount <= 2) {
				Logger::info("Removing directory $folder", [], __METHOD__, __LINE__);
				@rmdir($folder);

				$this->reporter()->add([
					'',
					$folder,
					file_exists($folder) ? 'Could not delete' : 'Deleted'
				]);
			} else if (($filecount == 3) && file_exists(trailingslashit($folder).'.DS_STORE')) {
				Logger::info("Removing .DS_STORE", [], __METHOD__, __LINE__);
				unlink(trailingslashit($folder).'.DS_STORE');

				Logger::info("Removing directory $folder", [], __METHOD__, __LINE__);
				@rmdir($folder);

				$this->reporter()->add([
					'',
					$folder,
					file_exists($folder) ? 'Could not delete' : 'Deleted'
				]);
			} else {
				Logger::info("NOT Removing directory $folder", [], __METHOD__, __LINE__);
			}
		}

		return true;
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

		if ($post_id == -1) {
			Logger::info("Cleaning empty directories.", [], __METHOD__, __LINE__);
			return $this->cleanEmptyDirectories();
		}

		if (!postIdExists($post_id)) {
			return true;
		}

		Logger::info("Processing $post_id", [], __METHOD__, __LINE__);

		$this->updateCurrentPost($post_id);

		$file = get_attached_file($post_id, true);
		$meta = wp_get_attachment_metadata($post_id, true);


		$baseDir = pathinfo($file, PATHINFO_DIRNAME);

		$filesToDelete = [$file];
		if (isset($meta['sizes'])) {
			foreach($meta['sizes'] as $size => $sizeData) {
				if (empty($sizeData['file'])) {
					continue;
				}

				$filesToDelete[] = trailingslashit($baseDir).basename($sizeData['file']);
			}
		}

		$og = arrayPath($meta, 'original_image', null);
		if (!empty($og)) {
			$filesToDelete[] = trailingslashit($baseDir).$og;
		}

		foreach($filesToDelete as $file) {
			if (file_exists($file)) {
				if (@unlink($file)) {
					Logger::info("Deleted $file", [], __METHOD__, __LINE__);
				} else {
					Logger::info("Error deleting $file", [], __METHOD__, __LINE__);
				}

				$this->reporter()->add([
					$post_id,
					$file,
					file_exists($file) ? 'Could not delete' : 'Deleted'
				]);
			} else {
				Logger::info("Skipping missing file: $file", [], __METHOD__, __LINE__);
				$this->reporter()->add([
					$post_id,
					$file,
					'Missing'
				]);
			}
		}

		Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

		return true;
	}

	//endregion
}
