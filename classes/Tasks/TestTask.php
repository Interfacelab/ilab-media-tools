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

class TestTask extends Task {
	private $stuff = [];

	public static function identifier() {
		return 'test-task';
	}

	public static function title() {
		return "Test Task";
	}

	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [
			'items' => [
				"title" => "Number of Items",
				"description" => "The number of simulated items to process for the task.",
				"type" => "number",
				"min" => 1,
				"max" => 20000,
				"step" => 1,
				"default" => 10
			],
			'sleep' => [
				"title" => "Sleep",
				"description" => "Sleeps the task for 1 second per item to simulate processing time.",
				"type" => "checkbox",
				"default" => false
			],
			'memory' => [
				"title" => "Test Memory",
				"description" => "Allocates a block of memory to test the task manager's memory handling when running tasks.",
				"type" => "checkbox",
				"default" => false
			],
			'errors' => [
				"title" => "Random Errors",
				"description" => "Generates a random error during processing.",
				"type" => "checkbox",
				"default" => false
			],
			'exceptions' => [
				"title" => "Random Exceptions",
				"description" => "Generates a random exception during processing.",
				"type" => "checkbox",
				"default" => false
			],
		];
	}

	public function prepare($options = [], $selectedItems = []) {
		$this->options = $options;

		$itemCount = arrayPath($options, 'items', 150);
		for($i = 0; $i < $itemCount; $i++) {
			$this->addItem(['item' => $i]);
		}

		return true;
	}

	public function performTask($item) {
		$tuid = arrayPath($this->options, 'tuid', null);
		Logger::info("Processing test item {$this->currentItem} ($tuid)", [], __METHOD__, __LINE__);

		if (arrayPath($this->options, 'sleep', false)) {
			Logger::info("Sleeping ...", [], __METHOD__, __LINE__);
			sleep(1);
		}

		if (arrayPath($this->options, 'memory', false)) {
			Logger::info("Testing Memory ...", [], __METHOD__, __LINE__);
			$this->stuff[] = str_repeat("0", 1048576 * rand(1, 2));
		}

		if (arrayPath($this->options, 'errors', false)) {
			if (rand(1,5) == 2) {
				Logger::info("Throwing an error ...", [], __METHOD__, __LINE__);
				return false;
			}
		}

		if (arrayPath($this->options, 'exceptions', false)) {
			if (rand(1,4) == 2) {
				Logger::info("Throwing an exception...", [], __METHOD__, __LINE__);
				throw new \Exception('Random exception');
			}
		}

		return true;
	}
}