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

namespace MediaCloud\Plugin\CLI;

use MediaCloud\Plugin\Tasks\Task;

if (!defined('ABSPATH')) { header('Location: /'); die; }

abstract class Command extends \WP_CLI_Command {
	public static function Warn($string) {
		\WP_CLI::warning(\WP_CLI::colorize($string));
	}

	public static function Error($string) {
		\WP_CLI::error(\WP_CLI::colorize($string));
	}

	public static function Info($string, $newline = false) {
		\WP_CLI::out(\WP_CLI::colorize($string).($newline ? "\n" : ""));
	}

	public static function Out($string, $newline = false) {
		\WP_CLI::out(\WP_CLI::colorize($string).($newline ? "\n" : ""));
	}

	/**
	 * @param Task $task
	 * @param array $options
	 * @param array $selected
	 *
	 * @throws \Exception
	 */
	protected function runTask($task, $options = [], $selected = []) {
		$task->cli = true;
		$task->setHandlers(function($message, $newLine) {
			Command::Info($message, $newLine);
		}, function($message) {
			Command::Error($message);
		});

		$task->prepare($options);
		if ($task->totalItems == 0) {
			self::Warn("No items found to process.  Exiting.");
			exit(0);
		}

		$task->wait();
		$task->dumpExisting();
		$task->loadNextData();

		Command::Out("", true);
		Command::Info("Found %W{$task->totalItems}%n items.", true);

		while(true) {
			$result = $task->run();
			if (intval($result) >= Task::TASK_COMPLETE) {
				$task->cleanUp();
				break;
			}
		}

		Command::Info("Complete.", true);
		Command::Out("", true);
	}

	public abstract static function Register();
}
