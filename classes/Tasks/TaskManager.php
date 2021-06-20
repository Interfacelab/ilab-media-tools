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

namespace MediaCloud\Plugin\Tasks;

use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\arrayPath;

/**
 * Manages background tasks
 */
final class TaskManager {
	/** @var TaskSettings  */
	private $settings = null;

	/**
	 * Task class registry
	 * @var string[]
	 */
	private static $registeredTasks = [];

	/**
	 * Current instance
	 * @var TaskManager|null
	 */
	private static $instance;

	/**
	 * Currently running tasks
	 * @var Task[]
	 */
	private $runningTasks = [];

	//region Init

	/**
	 * TaskManager constructor.
	 */
	private function __construct() {
		$this->settings = TaskSettings::instance();

		TaskDatabase::init();

		static::registerTask(TestTask::class);

		$this->runningTasks = (is_admin() || defined('DOING_CRON')) ? Task::runningTasks() : [];
		$this->setupHooks();
	}

	/**
	 * The static TaskManager instance
	 * @return TaskManager|null
	 */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new TaskManager();
		}

		return static::$instance;
	}
	//endregion

	//region Properties

	/**
	 * @return Task[]
	 */
	public function runningTasks() {
		return $this->runningTasks;
	}

	//endregion

	//region hooks

	/**
	 * Setup any required hooks
	 */
	private function setupHooks() {
		add_filter( 'cron_schedules', function($schedules) {
			$schedules['mcloud_batch_interval'] = [
				'interval' => 60,
				'display' => 'Media Cloud Batch Interval'
			];

			return $schedules;
		});

		add_action('mcloud_run_batch', [$this, 'handleCron']);
		add_action('wp_ajax_mcloud_task_heartbeat', [$this, 'handleHeartbeat']);

		if (!wp_next_scheduled('mcloud_run_batch')) {
			wp_schedule_event(time(), 'mcloud_batch_interval', 'mcloud_run_batch');
		}

		add_action('wp_ajax_nopriv_mcloud_run_task', [$this, 'actionRunTask']);
		add_action('wp_ajax_mcloud_run_task', [$this, 'actionRunTask']);

		if (is_admin()) {
			add_action('wp_ajax_mcloud_start_task', [$this, 'actionStartTask']);
			add_action('wp_ajax_mcloud_cancel_task', [$this, 'actionCancelTask']);
			add_action('wp_ajax_mcloud_cancel_all_tasks', [$this, 'actionCancelAllTasks']);
			add_action('wp_ajax_mcloud_nuke_all_tasks', [$this, 'actionNukeAllTasks']);
			add_action('wp_ajax_mcloud_task_status', [$this, 'actionTaskStatus']);
			add_action('wp_ajax_mcloud_all_task_statuses', [$this, 'actionAllTaskStatuses']);


			add_action('wp_ajax_mcloud_delete_scheduled_task', [$this, 'actionDeleteScheduledTask']);
			add_action('wp_ajax_mcloud_execute_scheduled_task', [$this, 'actionExecuteScheduledTask']);

			add_action('wp_ajax_mcloud_clear_task_history', [$this, 'actionClearTaskHistory']);
		}

		add_action('wp_ajax_testTaskStart', [$this, 'testTaskStart']);

		add_action('admin_init', function() {
			add_filter('bulk_actions-upload', function($actions) {
				foreach(static::$registeredTasks as $identifier => $taskClass) {
					if (empty($taskClass::bulkActionTitle())) {
						continue;
					}

					$actions[$identifier] = $taskClass::bulkActionTitle();
				}

				return $actions;
			});

			add_filter('handle_bulk_actions-upload', function($redirect_to, $action_name, $post_ids) {
				if (in_array($action_name, array_keys(static::$registeredTasks))) {
					$taskClass = static::$registeredTasks[$action_name];

					if ($this->settings->scheduleBulkActions) {
						$task = TaskSchedule::nextScheduledTaskOfType($taskClass::identifier());
						if (!empty($task)) {
							$task->selection = array_merge($task->selection, $post_ids);
							$task->save();
						} else {
							$taskClass::scheduleIn($this->settings->scheduleBulkActionsDelay, [], $post_ids);
						}

						$redirect_to = admin_url('admin.php?page=media-cloud-task-manager');
					} else {
						$task = new $taskClass();
						$task->prepare([], $post_ids);
						$this->queueTask($task);

						$redirect_to = admin_url('admin.php?page=mcloud-task-'.$taskClass::identifier());
					}
				}

				return $redirect_to;
			}, 1000, 3);
		});
	}
	//endregion

	//region actions

	private function closeClientConnection() {
		session_write_close();
		ignore_user_abort(true);

		if (function_exists('fastcgi_finish_request') && version_compare(phpversion(), '7.0.16', '>=')) {
			header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
			header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
			fastcgi_finish_request();
		}
	}

	public function actionRunTask() {
		Logger::info("Run Task Ajax", [], __METHOD__, __LINE__);
		$this->closeClientConnection();

		check_ajax_referer('mcloud_run_task', 'nonce');

		$taskId = arrayPath($_REQUEST, 'taskId', null);
		if (empty($taskId)) {
			Logger::info("Task is not defined.  Dying.", [], __METHOD__, __LINE__);
			wp_die();
		}

		$token = arrayPath($_REQUEST, 'token', null);
		if (!empty($token)) {
			$tokenVal = arrayPath($_REQUEST, 'tokenVal', null);
			if (!empty($tokenVal)) {
				Logger::info("Sending ACK", [], __METHOD__, __LINE__);

				TaskDatabase::setToken($token, $tokenVal);
			}
		}

		Logger::info("Running task {$taskId}", [], __METHOD__, __LINE__);
		$this->runTask($taskId);
	}

	/**
	 * Runs the cron job
	 * @throws \Exception
	 */
	public function handleCron() {
		update_site_option('mcloud_last_cron', time());

		foreach($this->runningTasks as $task) {
			if (!empty($task->cli)) {
				continue;
			}

			$this->runTask($task);
		}

		/** @var TaskSchedule[] $scheduled */
		$scheduled = TaskSchedule::scheduledTasks();
		foreach($scheduled as $item) {
			$item->runIfNeeded();
		}
	}

	/**
	 * Handles heartbeat
	 *
	 * @param bool $canDie
	 *
	 * @throws \Exception
	 */
	public function handleHeartbeat($canDie = true) {
		$freq = get_site_option('mcloud-tasks-heartbeat-frequency', 15);
		$lastHeartbeat = get_site_option('mcloud_last_heartbeat', 0);
		$delta = microtime(true) - $lastHeartbeat;
		if (($lastHeartbeat > 0) && ($delta < $freq)) {
			if ($canDie) {
				wp_die();
			} else {
				return;
			}
		}

		update_site_option('mcloud_last_heartbeat', microtime(true));

		$this->closeClientConnection();

		foreach($this->runningTasks as $task) {
			if (!empty($task->cli)) {
				continue;
			}

			$this->runTask($task);
		}

		$lastCron = get_site_option('mcloud_last_cron', 0);
		if (($lastCron > 0) && (time() - $lastCron < 120)) {
			return;
		}

		/** @var TaskSchedule[] $scheduled */
		$scheduled = TaskSchedule::scheduledTasks();
		foreach($scheduled as $item) {
			$item->runIfNeeded();
		}
	}

	/**
	 * Checks the status of a specific task
	 */
	public function actionStartTask() {
		Logger::info("Starting Task ... ", [], __METHOD__, __LINE__);
		check_ajax_referer('mcloud_start_task', 'nonce');
		Logger::info("Nonce verified.", [], __METHOD__, __LINE__);

		$taskId = arrayPath($_REQUEST, 'taskId', null);
		if (empty($taskId)) {
			wp_send_json(['status' => 'error', 'message' => 'Invalid task ID']);
		}

		if (!isset(static::$registeredTasks[$taskId])) {
			wp_send_json(['status' => 'error', 'message', "Unknown task type '{$taskId}'"]);
		}

		$options = arrayPath($_REQUEST, 'options', []);

		$taskClass = static::$registeredTasks[$taskId];
		$taskClass::markConfirmed();

		/** @var Task $task */
		$task = new $taskClass();
		$result = $task->prepare($options);
		if (!$result) {
			wp_send_json(['status' => 'error', 'message' => 'There are no items to process.']);
		}

		$this->queueTask($task);
		wp_send_json(['status' => 'ok', 'task' => $task]);
	}

	/**
	 * Checks the status of a specific task
	 */
	public function actionCancelTask() {
		Logger::info("Cancelling Task ... ", [], __METHOD__, __LINE__);
		check_ajax_referer('mcloud_cancel_task', 'nonce');
		Logger::info("Nonce verified.", [], __METHOD__, __LINE__);

		$taskId = arrayPath($_REQUEST, 'taskId', null);
		if (empty($taskId)) {
			wp_send_json(['status' => 'error', 'message' => 'Invalid task ID']);
		}

		$task = Task::instance($taskId);
		if (empty($task)) {
			Logger::info("No running task with task id $taskId", [], __METHOD__, __LINE__);
			wp_send_json(['status' => 'error', 'message', 'Unknown task.']);
		}

		Logger::info("Cancelling task {$taskId} ...", [], __METHOD__, __LINE__);
		$task->cancel();

		if (!empty($task->cli)) {
			$task->state = Task::STATE_CANCELLED;
			$task->updateTiming();
			$task->save();
		}

		wp_send_json(['status' => 'ok', 'message', 'Task cancelled.']);
	}

	/**
	 * Cancels all running tasks
	 */
	public function actionCancelAllTasks() {
		Logger::info("Cancelling All Tasks ... ", [], __METHOD__, __LINE__);
		check_ajax_referer('mcloud_cancel_all_tasks', 'nonce');
		Logger::info("Nonce verified.", [], __METHOD__, __LINE__);

		$tasks = static::runningTasks();
		foreach($tasks as $task) {
			Logger::info("Cancelling task {$task->id()} ...", [], __METHOD__, __LINE__);
			$task->cancel();

			if (!empty($task->cli)) {
				$task->state = Task::STATE_CANCELLED;
				$task->updateTiming();
				$task->save();
			}
		}

		wp_send_json(['status' => 'ok', 'message', 'Tasks cancelled.']);
	}

	/**
	 * Cancels all running tasks
	 */
	public function actionNukeAllTasks() {
		Logger::info("Nuking All Tasks ... ", [], __METHOD__, __LINE__);
		check_ajax_referer('mcloud_nuke_all_tasks', 'nonce');

		// SQL TO DELETE IT ALL EVERYTHING
		TaskDatabase::nukeData();

		wp_send_json(['status' => 'ok', 'message', 'Tasks cancelled.']);
	}


	public function actionClearTaskHistory() {
		Logger::info("Clearing Task History ... ", [], __METHOD__, __LINE__);
		check_ajax_referer('mcloud_clear_task_history', 'nonce');
		Logger::info("Nonce verified.", [], __METHOD__, __LINE__);

		global $wpdb;
		$query = "delete from {$wpdb->base_prefix}mcloud_task where state >= 100";
		$wpdb->query($query);

		wp_send_json(['status' => 'ok', 'message', 'History cleared.']);
	}


	/**
	 * Checks the status of a specific task
	 */
	public function actionTaskStatus() {
		check_ajax_referer('mcloud_task_status', 'nonce');

		$taskId = arrayPath($_REQUEST, 'taskId', null);
		if (empty($taskId)) {
			wp_send_json(['status' => 'error', 'message' => 'Invalid task ID']);
		}

		$task = Task::instance($taskId);
		if (empty($task)) {
			wp_send_json(['status' => 'error', 'message', 'Unknown task.']);
		}

		wp_send_json(['status' => 'ok', 'task' => $task]);
	}

	public function actionAllTaskStatuses() {
		check_ajax_referer('mcloud_task_status', 'nonce');

		$completeTasks = [];
		$newTasks = [];
		$currentTasks = [];

		$runningTasks = Task::runningTasks();
		$currentTaskIds = arrayPath($_REQUEST, 'currentTaskIds', []);
		$runningTaskIds = [];
		foreach($runningTasks as $runningTask) {
			$runningTaskIds[] = $runningTask->id();
			if (!in_array($runningTask->id(), $currentTaskIds)) {
				$newTasks[] = $runningTask;
			} else {
				$currentTasks[] = $runningTask;
			}
		}

		$completeTaskIds = array_diff($currentTaskIds, $runningTaskIds);
		foreach($completeTaskIds as $completeTaskId) {
			$task = Task::instance($completeTaskId);
			if (!empty($task)) {
				$completeTasks[] = $task;
			}
		}

		$newScheduled = [];
		$currentScheduled = [];

		$runningScheduled = TaskSchedule::scheduledTasks();
		$currentScheduledIds = arrayPath($_REQUEST, 'currentScheduledIds', []);
		$runningScheduledIds = [];
		foreach($runningScheduled as $scheduled) {
			$runningScheduledIds[] = $scheduled->id();
			if (!in_array($scheduled->id(), $currentScheduledIds)) {
				$newScheduled[] = $scheduled;
			} else {
				$currentScheduled[] = $scheduled;
			}
		}

		$deletedScheduledIds = array_values(array_diff($currentScheduledIds, $runningScheduledIds));


		wp_send_json([
			'status' => 'ok',
			'currentTasks' => $currentTasks,
			'newTasks' => $newTasks,
			'completeTasks' => $completeTasks,
			'newScheduled' => $newScheduled,
			'currentScheduled' => $currentScheduled,
			'deletedScheduledIds' => $deletedScheduledIds
		]);

	}

	public function actionDeleteScheduledTask() {
		check_ajax_referer('mcloud_delete_scheduled_task', 'nonce');

		$taskId = arrayPath($_REQUEST, 'taskId', null);
		if (empty($taskId)) {
			wp_send_json(['status' => 'error', 'message' => 'Invalid task ID']);
		}

		$task = TaskSchedule::instance($taskId);
		if (empty($task)) {
			wp_send_json(['status' => 'error', 'message', 'Unknown task.']);
		}

		$task->delete();
		wp_send_json(['status' => 'ok']);
	}

	public function actionExecuteScheduledTask() {
		check_ajax_referer('mcloud_execute_scheduled_task', 'nonce');

		$taskId = arrayPath($_REQUEST, 'taskId', null);
		if (empty($taskId)) {
			wp_send_json(['status' => 'error', 'message' => 'Invalid task ID']);
		}

		$task = TaskSchedule::instance($taskId);
		if (empty($task)) {
			wp_send_json(['status' => 'error', 'message', 'Unknown task.']);
		}

		$task->runNow();
		wp_send_json(['status' => 'ok']);
	}

	//endregion


	//region Registration

	/**
	 * Registers a background task class
	 *
	 * @param string $taskClass
	 * @throws \Exception
	 */
	public static function registerTask($taskClass) {
		static::$registeredTasks[$taskClass::identifier()] = $taskClass;
	}

	/**
	 * Returns the registered class for a task identifier
	 * @param $taskIdentifier
	 *
	 * @return string|null
	 */
	public static function taskClass($taskIdentifier) {
		if (isset(static::$registeredTasks[$taskIdentifier])) {
			return static::$registeredTasks[$taskIdentifier];
		}

		return null;
	}

	/**
	 * Returns the list of registered tasks
	 *
	 * @return string[]
	 */
	public static function registeredTasks() {
		return static::$registeredTasks;
	}


	//endregion

	//region Running Tasks

	/**
	 * Queues a task to run
	 *
	 * @param Task $task
	 *
	 * @throws \Exception
	 */
	public function queueTask($task) {
		Logger::info("Queueing task ...", [], __METHOD__, __LINE__);
		$task->wait();
		TaskRunner::dispatch($task);
	}

	/**
	 * Runs a task with the given identifier
	 *
	 * @param Task|int $taskOrId
	 *
	 * @throws \Exception
	 */
	public function runTask($taskOrId) {
		if (count($this->runningTasks) == 0) {
			Logger::info("No running tasks.", [], __METHOD__, __LINE__);
			return;
		}

		$task = null;

		if ($taskOrId instanceof Task) {
			$task = $taskOrId;
		} else if (is_numeric($taskOrId) || is_string($taskOrId)) {
			foreach($this->runningTasks as $runningTask) {
				if ($runningTask->id() == $taskOrId) {
					$task = $runningTask;
					break;
				}
			}
		}

		if (empty($task)) {
			Logger::info("No task with with id '{$taskOrId}'.", [], __METHOD__, __LINE__);
			return;
		}

		if ($this->settings->taskLimit > 0) {
			if (!Task::canRunTask($task->id(), $this->settings->taskLimit)) {
				Logger::info("Too many tasks running currently to run ".$task->id()." limit is ".$this->settings->taskLimit, [], __METHOD__, __LINE__);
				return;
			}
		}

		if ($task->state === Task::STATE_PREPARING) {
			Logger::info("Task is preparing, exiting.", [], __METHOD__, __LINE__);
			return;
		}

		if ($task->locked()) {
			Logger::info("Task already running, exiting.", [], __METHOD__, __LINE__);
			return;
		}

		if ($task->state >= Task::STATE_COMPLETE) {
			Logger::info("Task is already completed, exiting.", [], __METHOD__, __LINE__);
			return;
		}

		Logger::info("Running task.", [], __METHOD__, __LINE__);
		$result = $task->run();
		Logger::info("Result: $result", [], __METHOD__, __LINE__);
		$complete = false;
		if (intval($result) >= Task::TASK_COMPLETE) {
			Logger::info("Result: $result >= ".Task::TASK_COMPLETE."?", [], __METHOD__, __LINE__);
			$task->cleanup();
			Logger::info("Task complete.", [], __METHOD__, __LINE__);
			$complete = true;
		}

		if (empty($complete)) {
			Logger::info("Dispatching again.", [], __METHOD__, __LINE__);
			TaskRunner::dispatch($task);
		}
	}

	//endregion

	//region Test Tasks

	public function testTaskStart() {
		$task = new TestTask();

		for($i = 0; $i < 25000; $i++) {
			$task->addItem(['item' => $i]);
		}

		$this->queueTask($task);

		wp_send_json(['status' => 'waiting', 'taskId' => $task->id()]);
	}

	//endregion
}