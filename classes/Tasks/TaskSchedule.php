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

use MediaCloud\Plugin\Model\Model;
use MediaCloud\Vendor\Carbon\Carbon;
use MediaCloud\Vendor\Cron\CronExpression;
use MediaCloud\Vendor\Lorisleiva\CronTranslator\CronParsingException;
use MediaCloud\Vendor\Lorisleiva\CronTranslator\CronTranslator;
use function MediaCloud\Plugin\Utilities\gen_uuid;

/**
 * Represents the data for the task
 *
 * @property bool $recurring
 * @property string $tuid
 * @property int $lastRun
 * @property int $nextRun
 * @property string $taskType
 * @property string $schedule
 * @property array $options
 * @property array $selection
 */
class TaskSchedule extends Model implements \JsonSerializable {
	//region Fields

	/**
	 * The distinct uid for this particular schedule
	 * @var bool
	 */
	protected $tuid = null;

	/**
	 * Determines if the task is recurring
	 * @var bool
	 */
	protected $recurring = false;

	/**
	 * The time this was last run
	 * @var int|null
	 */
	protected $lastRun = null;

	/**
	 * For non-recurring, the time of the next run.
	 * @var int|null
	 */
	protected $nextRun = null;

	/**
	 * The crontab formatted schedule
	 * @var string
	 */
	protected $schedule = '* * * * *';

	/**
	 * The task type to run
	 * @var string
	 */
	protected $taskType = null;

	/**
	 * The task options
	 * @var array
	 */
	protected $options = [];

	/**
	 * The selected items to process
	 * @var array
	 */
	protected $selection = [];

	protected $modelProperties = [
		'recurring' => '%d',
		'tuid' => '%s',
		'lastRun' => '%d',
		'nextRun' => '%d',
		'taskType' => '%s',
		'schedule' => '%s',
		'options' => '%s',
		'selection' => '%s'
	];

	protected $serializedProperties = [
		'options',
		'selection'
	];

	//endregion

	//region Static

	public static function table() {
		global $wpdb;
		return "{$wpdb->base_prefix}mcloud_task_schedule";
	}

	//endregion

	//region Init
	public function __construct($data = null) {
		parent::__construct($data);

		if (empty($this->tuid)) {
			$this->tuid = gen_uuid(12);
		}
	}
	//endregion

	//region Properties

	/**
	 * Description of this task schedule in English
	 *
	 * @return string
	 */
	public function description() {
		if ($this->recurring && !empty($this->schedule)) {
			try {
				return CronTranslator::translate($this->schedule);
			} catch (CronParsingException $ex) {
				return 'Invalid cron format';
			}
		} else if ($this->nextRun > 0) {
			return 'Run once on '.date('F jS, Y', $this->nextRun).' at '.date('g:i a', $this->nextRun);
		} else {
			return 'Not configured.';
		}
	}

	/**
	 * Returns the next run date.
	 *
	 * @return Carbon|null
	 * @throws \Exception
	 */
	public function nextRunDate() {
		if ($this->recurring && !empty($this->schedule)) {
			$cron = CronExpression::factory($this->schedule);
			$date = $cron->getNextRunDate();
			if (!empty($date)) {
				return Carbon::instance($date);
			}

			return null;
		} else if ($this->nextRun > 0) {
			return Carbon::createFromTimestamp($this->nextRun);
		} else {
			return null;
		}
	}

	public function nextRunDateDescription() {
		if ($this->nextRun > 0) {
			$nextRunDate = Carbon::createFromTimestamp($this->nextRun);
			$nextRun = $nextRunDate->diffForHumans(Carbon::now(), true, false, 2);

			if ($this->nextRun >= time()) {
				$nextRun = "In {$nextRun}";
			} else {
				$nextRun = "{$nextRun} ago";
			}
		} else {
			$nextRun = '';
		}

		return $nextRun;
	}

	/**
	 * Returns the last run date.
	 *
	 * @return Carbon|null
	 * @throws \Exception
	 */
	public function lastRunDate() {
		if ($this->lastRun > 0) {
			return Carbon::createFromTimestamp($this->lastRun);
		}

		return null;
	}

	public function lastRunDateDescription() {
		if ($this->lastRun > 0) {
			$lastRunDate = Carbon::createFromTimestamp($this->lastRun);
			$lastRun = $lastRunDate->diffForHumans(Carbon::now(), true, false, 2).' ago';
		} else {
			$lastRun = '';
		}

		return $lastRun;
	}

	/**
	 * The last date this task should have been run, if recurring
	 *
	 * @return Carbon|null
	 */
	public function lastScheduledRunDate() {
		if ($this->recurring && !empty($this->schedule)) {
			$cron = CronExpression::factory($this->schedule);
			$date = $cron->getPreviousRunDate();
			if (!empty($date)) {
				return Carbon::instance($date);
			}
		}

		return null;
	}

	/**
	 * Returns the class name for the Task this schedule will trigger
	 *
	 * @return string|null
	 */
	public function taskClass() {
		return TaskManager::taskClass($this->taskType);
	}

	/**
	 * The title of the task that this schedule will trigger
	 *
	 * @return string|null
	 */
	public function taskTitle() {
		$taskClass = TaskManager::taskClass($this->taskType);
		if (!empty($taskClass)) {
			return $taskClass::title();
		}

		return null;
	}

	//endregion


	//region Running
	/**
	 * Determines if the task should be run
	 *
	 * @return bool
	 */
	public function shouldRun() {
		if (Task::scheduledTaskIsRunning($this->tuid)) {
			return false;
		}

		if ($this->recurring) {
			if (empty($this->schedule)) {
				return false;
			}

			if (time() >= $this->nextRun) {
				return true;
			}
		} else {
			if (empty($this->nextRun)) {
				return false;
			}

			if (time() >= $this->nextRun) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Runs the task if it's time to run it.
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function runIfNeeded() {
		if (!$this->shouldRun()) {
			return false;
		}

		$this->runNow();
	}

	/**
	 * Runs the task now, regardless of schedule
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function runNow() {
		if ($this->recurring) {
			$cron = CronExpression::factory($this->schedule);
			$this->nextRun = $cron->getNextRunDate('now')->getTimestamp();
			$this->lastRun = time();
			$this->save();
		}

		$taskClass = TaskManager::taskClass($this->taskType);
		if (empty($taskClass)) {
			return false;
		}

		$task = new $taskClass();
		$task->tuid = $this->tuid;
		$task->prepare($this->options, $this->selection);
		TaskManager::instance()->queueTask($task);

		if (!$this->recurring) {
			$this->delete();
		}
	}

	//endregion

	//region Queries

	/**
	 * Fetches all of the scheduled tasks
	 * @return TaskSchedule[]
	 */
	public static function scheduledTasks() {
		global $wpdb;

		$results = [];
		$table = "{$wpdb->base_prefix}mcloud_task_schedule";
		$rows = $wpdb->get_results("select * from {$table}");
		foreach($rows as $row) {
			$results[] = new static($row);
		}

		return $results;
	}

	/**
	 * @param $type
	 * @param int $safetyMargin
	 *
	 * @return TaskSchedule|null
	 */
	public static function nextScheduledTaskOfType($type, $safetyMargin = 1) {
		global $wpdb;

		$table = "{$wpdb->base_prefix}mcloud_task_schedule";
		$sql = $wpdb->prepare("select * from {$table} where taskType = %s and nextRun >= %d order by nextRun asc limit 1", $type, time() + ($safetyMargin * 60));
		$rows = $wpdb->get_results($sql);
		foreach($rows as $row) {
			return new static($row);
		}

		return null;
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public static function hasScheduledTaskOfType($type) {
		global $wpdb;

		$table = "{$wpdb->base_prefix}mcloud_task_schedule";
		$sql = $wpdb->prepare("select count(id) from {$table} where taskType = %s", $type);
		$count = $wpdb->get_var($sql);

		return ($count > 0);
	}

	//endregion

	//region JSON
	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'tuid' => $this->tuid,
			'recurring' => boolval($this->recurring),
			'lastRun' => $this->lastRunDateDescription(),
			'nextRun' => $this->nextRunDateDescription(),
			'taskType' => $this->taskType,
			'taskTitle' => $this->taskTitle(),
			'description' => $this->description(),
			'cron' => $this->schedule,
			'statusNonce' => wp_create_nonce('mcloud_scheduled_task_status')
		];
	}
	//endregion
}
