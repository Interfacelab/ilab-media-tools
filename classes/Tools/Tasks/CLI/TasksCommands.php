<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Tools\Tasks\CLI;

use MediaCloud\Plugin\CLI\Command;
use MediaCloud\Plugin\Tasks\TaskSchedule;
use MediaCloud\Plugin\Tasks\TestTask;
use MediaCloud\Vendor\Carbon\Carbon;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\gen_uuid;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Manage tasks for Media Cloud
 */
class TasksCommands extends Command {

	/**
	 * Tests schedule
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : The number of tasks to schedule.
	 *
	 * [--minutes=<number>]
	 * : The time between tasks
	 *
	 * [--items=<number>]
	 * : The number of items in each task
	 *
	 * [--sleep]
	 * : Configures the task to sleep when running
	 *
	 * [--memory]
	 * : Configures the task to use memory when running
	 *
	 * [--errors]
	 * : Configures the task to return false randomly
	 *
	 * [--exceptions]
	 * : Configures the task to throw an exception randomly
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws \Exception
	 */
	public function createTestSchedules($args, $assoc_args) {

		$count = arrayPath($assoc_args, 'count', 1);
		$minutes = arrayPath($assoc_args, 'minutes', 1);
		$items = arrayPath($assoc_args, 'items', 5);
		$sleep = arrayPath($assoc_args, 'sleep', false);
		$memory = arrayPath($assoc_args, 'memory', false);
		$errors = arrayPath($assoc_args, 'errors', false);
		$exceptions = arrayPath($assoc_args, 'exceptions', false);

		$currentTime = $minutes;
		for($i = 0; $i < $count; $i++) {
			TestTask::scheduleIn($currentTime, ['tuid' => gen_uuid(8), 'items' => $items, 'sleep' => $sleep, 'memory' => $memory, 'errors' => $errors, 'exceptions' => $exceptions]);
			$currentTime++;
		}
	}

	/**
	 * Tests schedule
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws \Exception
	 */
	public function scheduled() {
		$scheduled = TaskSchedule::scheduledTasks();

		if (count($scheduled) == 0) {
			self::Error("No scheduled tasks.  Exiting.");
			exit(0);
		}

		foreach($scheduled as $item) {
			self::Info($item->taskTitle().": ".$item->description().' ... ', false);

			$dt = $item->nextRunDate();
			self::Info("Will run in ".$dt->diffForHumans(Carbon::now(), true, false, 2), true);
		}
	}


	/**
	 * Tests schedule
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws \Exception
	 */
	public function runScheduled() {
		$scheduled = TaskSchedule::scheduledTasks();

		if (count($scheduled) == 0) {
			self::Info("No scheduled tasks.  Exiting", true);
		}

		foreach($scheduled as $item) {
			if ($item->runIfNeeded()) {
				self::Info("Running {$item->taskTitle()}", true);
			}
		}
	}


	/**
	 * Tests schedule
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function clearScheduled() {
		global $wpdb;
		$wpdb->query("delete from {$wpdb->base_prefix}mcloud_task_schedule");
	}

	public static function Register() {
		\WP_CLI::add_command('mediacloud:tasks', __CLASS__);
	}

}