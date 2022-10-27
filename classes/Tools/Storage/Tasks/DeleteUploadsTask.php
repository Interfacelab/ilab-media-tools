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

use MediaCloud\Plugin\Tasks\Task;
use MediaCloud\Plugin\Utilities\Logging\Logger;

class DeleteUploadsTask extends Task {
	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'delete-uploads';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Delete Uploads';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return null;
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
		return false;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/delete-uploads';
	}

	public static function runFromTaskManager() {
		return false;
	}
	

	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [];
	}


	//endregion

	//region Execution

	public function prepare($options = [], $selectedItems = []) {
		foreach($selectedItems as $selectedItem) {
			if (file_exists($selectedItem)) {
				$this->addItem(['filepath' => $selectedItem]);
			} else {
				Logger::info("Skipping $selectedItem - does not exist.", [], __METHOD__, __FUNCTION__);
			}
		}

		$this->addItem(['filepath' => -1]);
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
			} else if (($filecount == 3) && file_exists(trailingslashit($folder).'.DS_STORE')) {
				Logger::info("Removing .DS_STORE", [], __METHOD__, __LINE__);
				unlink(trailingslashit($folder).'.DS_STORE');

				Logger::info("Removing directory $folder", [], __METHOD__, __LINE__);
				@rmdir($folder);
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
		$filepath = $item['filepath'];

		if ($filepath == -1) {
			return $this->cleanEmptyDirectories();
		}

		if (@unlink($filepath)) {
			Logger::info("Deleted $filepath", [], __METHOD__, __LINE__);
		} else {
			Logger::info("Error deleting $filepath", [], __METHOD__, __LINE__);
		}

		return true;
	}

	//endregion
}
