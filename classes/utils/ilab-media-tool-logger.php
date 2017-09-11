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

if (!defined('ABSPATH')) { header('Location: /'); die; }

require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-base.php');
require_once(ILAB_CLASSES_DIR.'/tasks/ilab-s3-import-process.php');

class ILabMediaToolLogger {
	private static $instance;
	private $logger = null;
	private $context = [];

	public function __construct() {
		$env = getenv('ILAB_MEDIA_DEBUGGING_ENABLED');
		$enabled=get_option("ilab-media-tool-enabled-debugging", $env);

		if ($enabled) {
			$level = get_option('ilab-media-s3-debug-logging-level', 'none');

			if ($level != 'none') {
				$realLevel = \Monolog\Logger::INFO;

				if ($level == 'warning') {
					$realLevel = \Monolog\Logger::WARNING;
				} else if ($level == 'error') {
					$realLevel = \Monolog\Logger::ERROR;
				}

				$this->logger = new \Monolog\Logger('ilab-media-tool');
				$this->logger->pushHandler(new \Monolog\Handler\ErrorLogHandler(\Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, $realLevel));

				$paperTrailEndPoint = get_option('ilab-media-s3-debug-papertrail-endpoint', false);
				$paperTrailPort = get_option('ilab-media-s3-debug-papertrail-port', false);

				if (!empty($paperTrailEndPoint) && !empty($paperTrailPort)) {
					if (function_exists('socket_create')) {
						$userId = get_option('ilab-media-s3-debug-papertrail-user-id', false);
						if (!empty($userId)) {
							$this->context=['user' => $userId];
						}

						$this->logger->pushHandler(new \Monolog\Handler\SyslogUdpHandler($paperTrailEndPoint, $paperTrailPort, LOG_USER, $realLevel));
					}
				}
			}
		}
	}

	public function doLogInfo($message, $context=[]) {
		if ($this->logger) {
			$this->logger->addInfo($message, array_merge($this->context, $context));
		}
	}

	public function doLogWarning($message, $context=[]) {
		if ($this->logger) {
			$this->logger->addWarning($message, array_merge($this->context, $context));
		}
	}

	public function doLogError($message, $context=[]) {
		if ($this->logger) {
			$this->logger->addError($message, array_merge($this->context, $context));
		}
	}

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
}