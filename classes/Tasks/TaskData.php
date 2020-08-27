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

/**
 * Represents the data for the task
 *
 * @property int $taskId
 * @property int $current
 * @property bool $complete
 * @property array $data
 */
class TaskData extends Model {
	//region Fields

	/**
	 * The owning task
	 * @var Task
	 */
	protected $task;

	/**
	 * The owning task id
	 * @var int|null
	 */
	protected $taskId = null;

	/**
	 * The current item
	 * @var int
	 */
	protected $current = 0;

	/**
	 * Determines if this task data chunk is complete
	 * @var int
	 */
	protected $complete = false;

	/**
	 * The task data
	 * @var array
	 */
	protected $data = [];

	protected $modelProperties = [
		'taskId' => '%d',
		'current' => '%d',
		'complete' => '%d',
		'data' => '%s'
	];

	protected $serializedProperties = [
		'data'
	];

	//endregion

	//region Static

	public static function table() {
		global $wpdb;
		return "{$wpdb->base_prefix}mcloud_task_data";
	}

	//endregion

	//region Constructor

	/**
	 * TaskData constructor.
	 *
	 * @param Task $task
	 * @param null $data
	 */
	public function __construct($task, $data = null) {
		parent::__construct($data);

		$this->task = $task;
		if (!empty($this->task->id())) {
			$this->taskId = $this->task->id();
		}
	}

	//endregion

	//region Data

	public function addItem($item) {
		$this->data[] = $item;
	}

	public function full() {
		$maxItems = apply_filters('media-cloud/batch/max-chunk-items', 30);
		return count($this->data) >= $maxItems;
	}

	public function complete() {
		if ($this->current >= count($this->data)) {
			return true;
		}

		return false;
	}

	public function nextItem() {
		if ($this->complete()) {
			return false;
		}

		$result = $this->data[$this->current];
		$this->current++;
		$this->save();

		return $result;
	}

	//endregion

	//region Saving/Loading

	public function delete() {
		parent::delete();
	}

	public function save() {
		if (!empty($this->task->id())) {
			$this->taskId = $this->task->id();
		}

		if (empty($this->taskId)) {
			return false;
		}

		return parent::save();
	}

	//endregion

	//region Queries

	/**
	 * @param Task $task
	 * @param int $limit
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function dataForTask($task, $limit = 0) {
		global $wpdb;

		$result = [];

		$dataTable = static::table();
		$limitQuery = ($limit !== 0) ? "limit {$limit}" : "";
		$results = $wpdb->get_results("select * from {$dataTable} where complete != 1 and taskId = {$task->id()} order by id asc {$limitQuery}");
		if (!empty($results)) {
			foreach($results as $taskData) {
				$result[] = new TaskData($task, $taskData);
			}
		}

		return $result;
	}

	/**
	 * @param Task $task
	 *
	 * @return int
	 * @throws \Exception
	 */
	public static function dataCountForTask($task) {
		global $wpdb;

		$dataTable = static::table();
		$results = $wpdb->get_var("select count(id) from {$dataTable} where complete != 1 and taskId = {$task->id()}");
		if (!empty($results)) {
			return $results;
		}

		return (int)0;
	}

	/**
	 * @param Task $task
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function deleteDataForTask($task) {
		global $wpdb;

		$dataTable = static::table();
		$wpdb->query("delete from {$dataTable} where taskId = {$task->id()}");

		return true;
	}
	//endregion

}