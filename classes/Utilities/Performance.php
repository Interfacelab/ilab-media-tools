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

namespace MediaCloud\Plugin\Utilities;

use MediaCloud\Plugin\Utilities\Logging\Logger;

final class Performance {
	private static $perfMarkers = [];
	private static $level = 0;
	private static $totalTime = 0;

	public static function start($marker) {
		if (!defined('MCLOUD_ENABLE_PERF') || (MCLOUD_ENABLE_PERF !== true)) {
			return;
		}

		$currentTime = '';
		if (static::$level === 0) {
			static::$totalTime = microtime(true);
		} else {
			$currentTime = number_format(microtime(true) - static::$totalTime, 10);
			$currentTime = "({$currentTime})";
		}

		static::$level++;
		static::$perfMarkers[$marker] = microtime(true);
		$indent = str_repeat("\t", max(static::$level - 1, 0));
		Logger::info("Timing: {$currentTime}{$indent}Start {$marker}", [], __METHOD__, __LINE__);
	}

	public static function end($marker) {
		if (!defined('MCLOUD_ENABLE_PERF') || (MCLOUD_ENABLE_PERF !== true)) {
			return;
		}
		if (isset(static::$perfMarkers[$marker])) {
			$currentTime = '';
			if (static::$level >= 2) {
				$currentTime = number_format(microtime(true) - static::$totalTime, 10);
				$currentTime = "({$currentTime})";
			}

			$indent = str_repeat("\t", max(static::$level - 1, 0));
			$time = number_format(microtime(true) - static::$perfMarkers[$marker], 10);
			Logger::info("Timing: {$currentTime}{$indent}End {$marker} => $time", [], __METHOD__, __LINE__);
			unset(static::$perfMarkers[$marker]);
		}

		static::$level--;
	}
}