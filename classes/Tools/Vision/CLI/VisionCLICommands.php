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

namespace MediaCloud\Plugin\Tools\Vision\CLI;

use MediaCloud\Plugin\CLI\Command;
use MediaCloud\Plugin\Tools\Rekognition\VisionTool;
use MediaCloud\Plugin\Tools\Vision\Tasks\ProcessVisionTask;
use function MediaCloud\Plugin\Utilities\arrayPath;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Commands related to the Vision Tool
 */
class VisionCLICommands extends Command {
	/**
	 * Processes the media library with the selected vision provider
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<number>]
	 * : The maximum number of items to process, default is infinity.
	 *
	 * [--offset=<number>]
	 * : The starting offset to process.  Cannot be used with page.
	 *
	 * [--page=<number>]
	 * : The starting offset to process.  Page numbers start at 1.  Cannot be used with offset.
	 *
	 * [--order-by=<string>]
	 * : The field to sort the items to be imported by. Valid values are 'date', 'title' and 'filename'.
	 * ---
	 * options:
	 *   - date
	 *   - title
	 *   - filename
	 * ---
	 *
	 * [--order=<string>]
	 * : The sort order. Valid values are 'asc' and 'desc'.
	 * ---
	 * default: asc
	 * options:
	 *   - asc
	 *   - desc
	 * ---
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws \Exception
	 */
	public function process($args, $assoc_args) {
		$options = $assoc_args;

		if (isset($options['limit'])) {
			if (isset($options['page'])) {
				$options['offset'] = max(0,($assoc_args['page'] - 1) * $assoc_args['limit']);
				unset($options['page']);
			}

		}

		if (isset($assoc_args['order-by'])) {
			$orderBy = $assoc_args['order-by'];
			$dir = arrayPath($assoc_args, 'order', 'asc');

			unset($assoc_args['order-by']);
			unset($assoc_args['order']);

			$assoc_args['sort-order'] = $orderBy.'-'.$dir;
		}

		$task = new ProcessVisionTask();
		$this->runTask($task, $options);
	}

	public static function Register() {
		\WP_CLI::add_command('mediacloud:vision', __CLASS__);
	}

}