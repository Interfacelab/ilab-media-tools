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

namespace ILAB\MediaCloud\Tasks;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;

/**
 * Handles the async ajax calls to dispatch tasks in the background
 */
final class TaskRunner {
	//region Fields

	/**
	 * Current instance
	 * @var TaskRunner|null
	 */
	private static $instance = null;

	private $verifySSL = 'default';
	private $connectTimeout = 0.01;
	private $timeout = 0.01;
	private $skipDNS = false;
	private $skipDNSHost = null;
	private $httpClientName = null;

	//endregion

	//region Constructor

	private function __construct() {
		$this->verifySSL = Environment::Option('mcloud-tasks-verify-ssl', null, 'no');
		$this->connectTimeout = Environment::Option('mcloud-tasks-connect-timeout', null, 0.01);
		$this->timeout = Environment::Option('mcloud-tasks-timeout', null, 0.01);
		$this->skipDNS = Environment::Option('mcloud-tasks-skip-dns', null, false);
		$this->skipDNSHost = Environment::Option('mcloud-tasks-skip-dns-host', null, 'ip');
		$this->httpClientName = Environment::Option('mcloud-tasks-http-client', null, 'wordpress');

		if (is_admin()) {
			add_action('wp_ajax_task_runner_test', [$this, 'testTaskRunner']);
		}
	}

	/**
	 * @return TaskRunner|null
	 */
	private static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public static function init() {
		static::instance();
	}

	//endregion

	//region Requests

	/**
	 * Makes the async call via Guzzle
	 *
	 * @param $url
	 * @param $args
	 * @param bool $timeoutOverride
	 *
	 * @return bool|\Exception|\Psr\Http\Message\ResponseInterface
	 */
	protected function postRequestGuzzle($url, $args, $timeoutOverride = false) {
		if ($this->skipDNS) {
			$ip = null;

			if ($this->skipDNSHost == 'ip') {
				$ip = getHostByName(getHostName());
				if (empty($ip)) {
					$ip = $_SERVER['SERVER_ADDR'];
				}
			}

			if (empty($ip)) {
				$ip = '127.0.0.1';
			}

			$host = parse_url($url, PHP_URL_HOST);
			$url = str_replace($host, $ip, $url);

			$headers = [
				'Host' => $host
			];

			if (isset($args['headers'])) {
				$args['headers'] = array_merge($args['headers'], $headers);
			} else {
				$args['headers'] = $headers;
			}

			$cookies = CookieJar::fromArray($args['cookies'], $ip);
		} else {
			$cookies = CookieJar::fromArray($args['cookies'], $_SERVER['HTTP_HOST']);
		}

		if ($this->verifySSL == 'default') {
			$verifySSL = apply_filters( 'https_local_ssl_verify', false);
		} else {
			$verifySSL = ($this->verifySSL == 'yes');
		}

		$rawUrl = esc_url_raw( $url );

		$options = [
			'synchronous' => !empty($args['blocking']),
			'cookies' => $cookies,
			'verify' => $verifySSL
		];

		if (isset($args['body'])) {
			if (is_array($args['body'])) {
				$options['form_params'] = $args['body'];
			} else {
				$options['body'] = $args['body'];
			}
		}

		if (!empty($headers)) {
			$options['headers'] = $headers;
		}

		if ($timeoutOverride !== false) {
			$options['timeout'] = $timeoutOverride;
			$options['connect_timeout'] = $timeoutOverride;
		} else {
			$options['timeout'] = max(0.01, $this->timeout);
			$options['connect_timeout'] =  max(0.01, $this->connectTimeout);;
		}

		$client = new Client();
		try {
			if (empty($options['synchronous'])) {
				Logger::info("Async call to $rawUrl");
				$options['synchronous'] = true;
				$client->postSync($rawUrl, $options);
				Logger::info("Async call to $rawUrl complete.");
				return true;
			} else {
				Logger::info("Synchronous call to $rawUrl");
				$result = $client->post($rawUrl, $options);
				Logger::info("Synchronous call to $rawUrl complete.");
				return $result;
			}
		} catch (\Exception $ex) {
			if (!empty($args['blocking'])) {
				return $ex;
			} else {
				Logger::info("Async exception: ".$ex->getMessage());
			}
		}

		return true;
	}

	protected function postRequestWordPress($url, $args, $timeoutOverride = false) {
		if ($this->skipDNS) {
			$ip = null;

			if ($this->skipDNSHost == 'ip') {
				$ip = getHostByName(getHostName());
				if (empty($ip)) {
					$ip = $_SERVER['SERVER_ADDR'];
				}
			}

			if (empty($ip)) {
				$ip = '127.0.0.1';
			}

			$host = parse_url($url, PHP_URL_HOST);
			$url = str_replace($host, $ip, $url);

			$headers = [
				'Host' => $host
			];

			if (isset($args['headers'])) {
				$args['headers'] = array_merge($args['headers'], $headers);
			} else {
				$args['headers'] = $headers;
			}
		}

		if ($this->verifySSL == 'default') {
			$verifySSL = apply_filters( 'https_local_ssl_verify', false);
		} else {
			$verifySSL = ($this->verifySSL == 'yes');
		}

		$rawUrl = esc_url_raw( $url );

		$args['sslverify'] = $verifySSL;

		if ($timeoutOverride !== false) {
			$args['timeout'] = $timeoutOverride;
		} else {
			$args['timeout'] = max(0.01, $this->timeout);
		}

		$result = wp_remote_post($rawUrl, $args);
		if (is_wp_error($result)) {
			return new \Exception($result->get_error_message());
		}

		return true;
	}

	//endregion


	//region Dispatching

	/**
	 * Dispatches a background task
	 *
	 * @param Task $task
	 * @throws \Exception
	 */
	public static function dispatch($task) {
		$url = admin_url('admin-ajax.php');

		$token = '__mcloud_token_'.uniqid(8);
		$tokenVal = uniqid(8);

		$queryArgs = [
			'action' => 'mcloud_run_task',
			'taskId' => $task->id(),
			'taskType' => $task::identifier(),
			'token' => $token,
			'tokenVal' => $tokenVal,
			'nonce' => wp_create_nonce('mcloud_run_task')
		];

		if (isset($_COOKIE['XDEBUG_SESSION'])) {
			$queryArgs['XDEBUG_SESSION'] = $_COOKIE['XDEBUG_SESSION'];
		}

		$url = add_query_arg($queryArgs, $url);

		Logger::info("Dispatching to {$url}.");

		$loops = 0;
		while(true) {
			$loops++;
			if ($loops > 1) {
				Logger::info("Loop #{$loops}!");
			}

			if ($loops == 2) {
				break;
			}

			if (static::instance()->httpClientName == 'guzzle') {
				static::instance()->postRequestGuzzle($url, [
					'blocking' => false,
					'cookies' => $_COOKIE
				]);
			} else {
				static::instance()->postRequestWordPress($url, [
					'blocking' => false,
					'cookies' => $_COOKIE
				]);
			}

			sleep(3);

			$val = Environment::WordPressOption($token);
			if ($val == $tokenVal) {
				Logger::info("ACK acknowledge!");
				delete_site_option($token);
				break;
			}
		}
	}

	//endregion

	//region Testing Connectivity

	/**
	 * Performs the actual test request
	 *
	 * @param $which
	 * @param $url
	 *
	 * @return bool|\Exception
	 */
	private static function postTestRequest($which, $url) {
		try {
			if ($which === 'guzzle') {
				static::instance()->postRequestGuzzle($url, ['blocking' => false, 'cookies' => $_COOKIE]);
			} else {
				static::instance()->postRequestWordPress($url, ['blocking' => false, 'cookies' => $_COOKIE]);
			}
		} catch (\Exception $ex) {
			return $ex;
		}

		$loops = 1;
		while ($loops < 10) {
			$testVal = Environment::WordPressOption('mcloud_connection_test');
			if ($testVal === 'test_value') {
				Logger::info("Connectivity test value fetched for '$which'.");
				return true;
			}

			Logger::info("Connectivity test value not found for '$which'.  Try #{$loops} ... trying again in 2 seconds.");
			sleep(2);
			$loops++;
		}

		Logger::info("Unable to get test value for '$which'.");

		return false;
	}

	/**
	 * Tests the connectivity for guzzle and wordpress in a specific order.
	 *
	 * @param $first
	 * @param $second
	 *
	 * @return string[]|bool
	 */
	private static function doTestConnectivity($first, $second) {
		delete_site_option('mcloud_connection_test');

		$url = admin_url('admin-ajax.php');
		$queryArgs = [
			'action' => 'task_runner_test',
			'nonce' => wp_create_nonce('task_runner_test')
		];

		if (isset($_COOKIE['XDEBUG_SESSION'])) {
			$queryArgs['XDEBUG_SESSION'] = $_COOKIE['XDEBUG_SESSION'];
		}

		$url = add_query_arg($queryArgs, $url);

		$errors = [];

		Logger::info("Testing connectivity using first '$first'");
		$result = static::postTestRequest($first, $url);
		if ($result instanceof \Exception) {
			Logger::info("Connectivity error using first '$first' => ".$result->getMessage());
			if (!in_array($result->getMessage(), $errors)) {
				$errors[] = $result->getMessage();
			}
		} else if (empty($result)) {
			Logger::info("Connectivity error using first '$first' => Could not dispatch background task.");
			$message = 'Unable to dispatch background task.';
			if (!in_array($message, $errors)) {
				$errors[] = $message;
			}
		} else {
			Logger::info("Testing connectivity first success");
			delete_site_option('mcloud_connection_test');
			return true;
		}

		if (!empty($errors)) {
			Logger::info("Testing Connectivity using second '$second'");
			$result = static::postTestRequest($second, $url);
			if ($result instanceof \Exception) {
				Logger::info("Connectivity error using second '$second' => ".$result->getMessage());
				if (!in_array($result->getMessage(), $errors)) {
					$errors[] = $result->getMessage();
				}
			} else if (empty($result)) {
				Logger::info("Connectivity error using second '$second' => Could not dispatch background task.");
				if (!in_array($message, $errors)) {
					$errors[] = $message;
				}
			} else {
				Logger::info("Testing connectivity second success");
				delete_site_option('mcloud_connection_test');
				Environment::UpdateOption('mcloud-tasks-http-client', $second);
				return true;
			}
		}

		return $errors;
	}

	/**
	 * Performs a background connection test
	 *
	 * @return bool|string[]
	 */
	public static function testConnectivity() {
		if (static::instance()->httpClientName === 'guzzle') {
			return static::doTestConnectivity('guzzle', 'wordpress');
		}

		return static::doTestConnectivity('wordpress', 'guzzle');
	}

	/**
	 * Ajax test endpoint
	 */
	public function testTaskRunner() {
		Logger::info("Test Task Runner");
		check_ajax_referer('task_runner_test', 'nonce');

		Logger::info("Updating test option.");
		Environment::UpdateOption('mcloud_connection_test', 'test_value');
	}

	//endregion
}