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

namespace ILAB\MediaCloud\Utilities\Logging;

use ILAB\MediaCloud\CLI\Command;
use ILAB\MediaCloud\Utilities\Environment;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger as MonologLogger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class Logger {
	//region Class variables
	private static $instance;
	private $logger = null;
	private $context = [];

	private $time = [];

	private $useWPCLI = false;
	//endregion

	//region Constructor
	public function __construct() {
        if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
	        $this->useWPCLI = (\WP_CLI::get_config('debug') == 'mediacloud');

	        if ($this->useWPCLI) {
                Command::Info('%WMedia Cloud Debugging Enabled', true);
            }
        }

		$enabled = ($this->useWPCLI) ?: Environment::Option("mcloud-tool-enabled-debugging", 'ILAB_MEDIA_DEBUGGING_ENABLED', false);

		if ($enabled) {
			$level = Environment::Option('mcloud-debug-logging-level', null, ($this->useWPCLI) ? 'info' : 'none');

			if ($level != 'none') {
				$realLevel = MonologLogger::INFO;

				if ($level == 'warning') {
					$realLevel = MonologLogger::WARNING;
				} else if ($level == 'error') {
					$realLevel = MonologLogger::ERROR;
				}

                if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
					$realLevel = MonologLogger::ERROR;
				}

				$this->logger = new MonologLogger('ilab-media-tool');
				$this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $realLevel));
				$this->logger->pushHandler(new DatabaseLoggerHandler($realLevel));
			}

            set_error_handler(function($errno, $errstr, $errfile, $errline, $errContext) {
                $this->logSystemError($errno, $errstr, $errfile, $errline);
                return false;
            });

            register_shutdown_function(function(){
                $lastError = error_get_last();
                if (!empty($lastError)) {
                    $this->logSystemError($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line']);
                }
            });
		}
	}
	//endregion

	//region Protected Logging Methods
    protected function logSystemError($type, $message, $file, $line) {
	    switch($type) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                $this->doLogError($message, ['file' => $file, 'line' => $line]);
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $this->doLogWarning($message, ['file' => $file, 'line' => $line]);
                break;
            default:
                $this->doLogInfo($message, ['file' => $file, 'line' => $line]);
                break;
        }
    }

	protected function doLogInfo($message, $context=[]) {
	    if ($this->useWPCLI) {
            Command::Info($message, true);
        }

		if ($this->logger) {
			$this->logger->addInfo($message, array_merge($this->context, $context));
		}
	}

	protected function doLogWarning($message, $context=[]) {
        if ($this->useWPCLI) {
            Command::Warn($message);
        }

		if ($this->logger) {
			$this->logger->addWarning($message, array_merge($this->context, $context));
		}
	}

	protected function doLogError($message, $context=[]) {
        if ($this->useWPCLI) {
            Command::Error($message." => ".((isset($context['exception'])) ? $context['exception'] : "No error message"));
        }

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