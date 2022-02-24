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

namespace MediaCloud\Plugin\Utilities\Logging;

use MediaCloud\Plugin\CLI\Command;
use MediaCloud\Plugin\Tools\Debugging\DebuggingToolSettings;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Ray\RayLoggerHandler;
use MediaCloud\Vendor\Monolog\Formatter\LineFormatter;
use MediaCloud\Vendor\Monolog\Handler\ErrorLogHandler;
use MediaCloud\Vendor\Monolog\Handler\HandlerInterface;
use MediaCloud\Vendor\Monolog\Handler\SyslogUdpHandler;
use MediaCloud\Vendor\Monolog\Logger as MonologLogger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class Logger {
	//region Class variables
	private static $instance;
	private $logger = null;
	private $context = [];

	private $time = [];

	private $useWPCLI = false;

	private $tempLoggers = [];

	/** @var DebuggingToolSettings  */
	private $settings = null;
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
			$this->settings = DebuggingToolSettings::instance();

			$level = $this->settings->debugLoggingLevel;

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

				$this->logger = new MonologLogger('media-cloud');
                $errorLogHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $realLevel);
                $errorLogHandler->setFormatter(new LineFormatter("%channel%.%level_name%: %message% %context% %extra%"));
				$this->logger->pushHandler($errorLogHandler);

				$papertrailHost = $this->settings->debugRemoteUrl;//Environment::Option('mcloud-debug-remote-url', null, null);
				$papertrailPort = $this->settings->debugRemotePort;//Environment::Option('mcloud-debug-remote-url-port', null, null);

				if (!empty($papertrailHost) && !empty($papertrailPort)) {
					$papertrail = new SyslogUdpHandler($papertrailHost, $papertrailPort);
					$papertrail->setFormatter(new LineFormatter("%channel%.%level_name%: %message%"));
					$this->logger->pushHandler($papertrail);
				} else {
					$this->logger->pushHandler(new DatabaseLoggerHandler($realLevel));
				}

				$this->logger->pushHandler(new QueryMonitorLoggerHandler($realLevel));

				if ((class_exists( 'Spatie\WordPressRay\Ray') || class_exists('\Spatie\Ray\Ray')) && !empty($this->settings->useRay)) {
					$this->logger->pushHandler(new RayLoggerHandler($realLevel));
				}
			}

			if (version_compare(phpversion(), '8', '>=')) {
				set_error_handler(function($errno, $errstr, $errfile, $errline) {
					$this->logSystemError($errno, $errstr, $errfile, $errline);
					return false;
				});
			} else {
				set_error_handler(function($errno, $errstr, $errfile, $errline, $errContext) {
					$this->logSystemError($errno, $errstr, $errfile, $errline);
					return false;
				});
			}

            register_shutdown_function(function(){
                $lastError = error_get_last();
                if (!empty($lastError)) {
                    $this->logSystemError($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line']);
                }
            });
		}
	}
	//endregion

	//region Temporary Loggers

	/**
	 * @param string $name
	 * @param HandlerInterface $logger
	 */
	public function addTemporaryLogger($name, $logger) {
		if (empty($this->logger)) {
			return;
		}

		$this->tempLoggers[$name] = $logger;
		$this->logger->pushHandler($logger);
	}

	public function removeTemporaryLogger($name) {
		if (empty($this->logger)) {
			return;
		}

		if (isset($this->tempLoggers[$name])) {
			$handler = $this->logger->popHandler();
			$otherLoggers = [];

			if ($handler != $this->tempLoggers[$name]) {
				$otherLoggers[] = $handler;
			}

			while (!empty($handler) && ($handler != $this->tempLoggers[$name])) {
				if (count($this->logger->getHandlers() == 0)) {
					$handler = null;
					break;
				}

				$otherLoggers[] = $handler = $this->logger->popHandler();
			}

			foreach($otherLoggers as $otherLogger) {
				$this->logger->pushHandler($otherLogger);
			}

			unset($this->tempLoggers[$name]);
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

	private function prepMessage($message, $function, $line, &$context) {
		if (!empty($function)) {

			$functionParts = explode('\\', $function);
			$function = array_pop($functionParts);
			$functionParts = explode('::', $function);
			$prefix = $function;
			if (count($functionParts) > 1) {
				$context['__class'] = array_shift($functionParts);
				$context['__method'] = array_shift($functionParts);
				$context['__line'] = intval($line);
				$prefix = '';
			} else {
				if (!empty($line)) {
					$prefix = $prefix.':'.$line.' ';
				}
			}

			$message = $prefix.$message;
		}

		$pid = @getmypid();
		if (!empty($pid)) {
			$message = "[$pid] ".$message;
		}

		return $message;
	}

	protected function doLogInfo($message, $context=[], $function = null, $line = null) {
		if (!empty($this->settings) && $this->settings->matchesFilter($message)) {
			return;
		}

	    if ($this->useWPCLI) {
            Command::Info($message, true);
        }

		if ($this->logger) {
			$message = $this->prepMessage($message, $function, $line, $context);
			$this->logger->info($message, array_merge($this->context, $context));
		}
	}

	protected function doLogWarning($message, $context=[], $function = null, $line = null) {
		if (!empty($this->settings) && $this->settings->matchesFilter($message)) {
			return;
		}

		if ($this->useWPCLI) {
            Command::Warn($message);
        }

		if ($this->logger) {
			$message = $this->prepMessage($message, $function, $line, $context);
			$this->logger->warning($message, array_merge($this->context, $context));
		}
	}

	protected function doLogError($message, $context=[], $function = null, $line = null) {
		if (!empty($this->settings) && $this->settings->matchesFilter($message)) {
			return;
		}

		if ($this->useWPCLI) {
            Command::Error($message." => ".((isset($context['exception'])) ? $context['exception'] : "No error message"));
        }

        if ($this->logger) {
	        $message = $this->prepMessage($message, $function, $line, $context);
	        $this->logger->error($message, array_merge($this->context, $context));
		}
	}

	protected function doStartTiming($message, $context=[], $function = null, $line = null) {
		if ($this->logger) {
			$this->time[] = microtime(true);
			$message = $this->prepMessage($message, $function, $line, $context);
			$this->logger->info($message, array_merge($this->context, $context));
		}
	}

	protected function doEndTiming($message, $context=[], $function = null, $line = null) {
		if ($this->logger) {
			$time = array_pop($this->time);
			$context['time'] = microtime(true) - $time;
			$message = $this->prepMessage($message, $function, $line, $context);
			$this->logger->info($message, array_merge($this->context, $context));
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

	public static function backtrace($context=[], $function = null, $line = null) {
		$stack = debug_backtrace(0);
		array_shift($stack);

		$stackJSON = "<pre>".esc_html(json_encode($stack, JSON_PRETTY_PRINT))."</pre>";
		Logger::info("Stack Dump:\n$stackJSON", $context, $function, $line);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param null|string $function
	 * @param null|string $line
	 */
	public static function info($message, $context=[], $function = null, $line = null) {
		self::instance()->doLogInfo($message, (empty($context) || !is_array($context)) ? [] : $context, $function, $line);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param null|string $function
	 * @param null|string $line
	 */
	public static function warning($message, $context=[], $function = null, $line = null) {
		self::instance()->doLogWarning($message, (empty($context) || !is_array($context)) ? [] : $context, $function, $line);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param null|string $function
	 * @param null|string $line
	 */
	public static function error($message, $context=[], $function = null, $line = null) {
		self::instance()->doLogError($message, (empty($context) || !is_array($context)) ? [] : $context, $function, $line);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param null|string $function
	 * @param null|string $line
	 */
	public static function startTiming($message, $context=[], $function = null, $line = null) {
		self::instance()->doStartTiming($message, (empty($context) || !is_array($context)) ? [] : $context, $function, $line);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param null|string $function
	 * @param null|string $line
	 */
	public static function endTiming($message, $context=[], $function = null, $line = null) {
		self::instance()->doEndTiming($message, (empty($context) || !is_array($context)) ? [] : $context, $function, $line);
	}
	//endregion
}
