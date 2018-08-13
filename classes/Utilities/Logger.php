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

namespace ILAB\MediaCloud\Utilities;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger as MonologLogger;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class Logger {
	//region Class variables
	private static $instance;
	private $logger = null;
	private $context = [];

	private $time = [];
	//endregion

	//region Constructor
	public function __construct() {
		$enabled=EnvironmentOptions::Option("ilab-media-tool-enabled-debugging", 'ILAB_MEDIA_DEBUGGING_ENABLED', false);

		if ($enabled) {
			$level = get_option('ilab-media-s3-debug-logging-level', 'none');

			if ($level != 'none') {
				$realLevel = MonologLogger::INFO;

				if ($level == 'warning') {
					$realLevel = MonologLogger::WARNING;
				} else if ($level == 'error') {
					$realLevel = MonologLogger::ERROR;
				}

				if ( defined( 'WP_CLI' ) && \WP_CLI ) {
					$realLevel = MonologLogger::ERROR;
				}

				$this->logger = new MonologLogger('ilab-media-tool');
				$this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $realLevel));

				$paperTrailEndPoint = EnvironmentOptions::Option('ilab-media-s3-debug-papertrail-endpoint', 'ILAB_MEDIA_S3_DEBUG_PAPERTRAIL_ENDPOINT', false);
				$paperTrailPort = EnvironmentOptions::Option('ilab-media-s3-debug-papertrail-port', 'ILAB_MEDIA_S3_DEBUG_PAPERTRAIL_PORT', false);

				if (!empty($paperTrailEndPoint) && !empty($paperTrailPort)) {
					if (function_exists('socket_create')) {
						$userId = get_option('ilab-media-s3-debug-papertrail-user-id', false);
						if (!empty($userId)) {
							$this->context=['user' => $userId];
						}

						$this->logger->pushHandler(new SyslogUdpHandler($paperTrailEndPoint, $paperTrailPort, LOG_USER, $realLevel));
					}
				}
			}
		}
	}
	//endregion

	//region Protected Logging Methods
	protected function doLogInfo($message, $context=[]) {
		if ($this->logger) {
			$this->logger->addInfo($message, array_merge($this->context, $context));
		}
	}

	protected function doLogWarning($message, $context=[]) {
		if ($this->logger) {
			$this->logger->addWarning($message, array_merge($this->context, $context));
		}
	}

	protected function doLogError($message, $context=[]) {
		if ($this->logger) {
			$this->logger->addError($message, array_merge($this->context, $context));
		}
	}

	protected function doStartTiming($message, $context=[]) {
		if ($this->logger) {
			$this->time[] = microtime(true);
			$this->logger->addInfo($message, array_merge($this->context, $context));
		}
	}

	protected function doEndTiming($message, $context=[]) {
		if ($this->logger) {
			$time = array_pop($this->time);
			$context['time'] = microtime(true) - $time;

			$this->logger->addInfo($message, array_merge($this->context, $context));
		}
	}
	//endregion

	//region Static Methods
	/**
	 * Returns the static instance
	 * @return Logger
	 */
	public static function instance() {
		if (!isset(self::$instance)) {
			$class=__CLASS__;
			self::$instance = new $class();
		}

		return self::$instance;
	}

	public static function info($message, $context=[]) {
		self::instance()->doLogInfo($message, (empty($context) || !is_array($context)) ? [] : $context);
	}

	public static function warning($message, $context=[]) {
		self::instance()->doLogWarning($message, (empty($context) || !is_array($context)) ? [] : $context);
	}

	public static function error($message, $context=[]) {
		self::instance()->doLogError($message, (empty($context) || !is_array($context)) ? [] : $context);
	}

	public static function startTiming($message, $context=[]) {
		self::instance()->doStartTiming($message, (empty($context) || !is_array($context)) ? [] : $context);
	}

	public static function endTiming($message, $context=[]) {
		self::instance()->doEndTiming($message, (empty($context) || !is_array($context)) ? [] : $context);
	}
	//endregion
}