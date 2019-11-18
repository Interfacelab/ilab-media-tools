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

namespace ILAB\MediaCloud\Tools\Storage\Tasks;

use ILAB\MediaCloud\Storage\StorageGlobals;
use ILAB\MediaCloud\Tasks\AttachmentTask;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use function ILAB\MediaCloud\Utilities\postIdExists;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class CleanUploadsTask extends AttachmentTask {
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
				Logger::info("Removing directory $folder");
				@rmdir($folder);
			} else if (($filecount == 3) && file_exists(trailingslashit($folder).'.DS_STORE')) {
				Logger::info("Removing .DS_STORE");
				unlink(trailingslashit($folder).'.DS_STORE');

				Logger::info("Removing directory $folder");
				@rmdir($folder);
			} else {
				Logger::info("NOT Removing directory $folder");
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
		if (!postIdExists($post_id)) {
			return true;
		}

		Logger::info("Processing $post_id");

		if ($post_id == -1) {
			return $this->cleanEmptyDirectories();
		}

		$this->updateCurrentPost($post_id);

		Logger::info("Processing $post_id");

		$file = get_attached_file($post_id, true);
		$meta = wp_get_attachment_metadata($post_id, true);

		$baseDir = pathinfo($file, PATHINFO_DIRNAME);

		$filesToDelete = [$file];
		foreach($meta['sizes'] as $size => $sizeData) {
			if (empty($sizeData['file'])) {
				continue;
			}

			$filesToDelete[] = trailingslashit($baseDir).basename($sizeData['file']);
		}

		foreach($filesToDelete as $file) {
			Logger::info("Deleting $file");
			@unlink($file);
		}

		Logger::info("Finished processing $post_id");

		return true;
	}

	//endregion
}