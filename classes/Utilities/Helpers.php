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

namespace MediaCloud\Plugin\Utilities {
	// As this file is automatically included if loaded through autoloader
	// do a check and avoid the direct access guard in that case.
	use MediaCloud\Plugin\Utilities\Logging\Logger;

	if (!defined('ABSPATH') && empty($GLOBALS['__composer_autoload_files'])) { header('Location: /'); die; }

	if (function_exists('\MediaCloud\Plugin\Utilities\vomit')) {
		return;
	}

	/**
	 * Brute force debug tool
	 * @param $what
	 * @param bool|true $die
	 */
	function vomit($what, $die=true)
	{
		echo "<pre>";
		print_r($what);
		echo "</pre>";

		if ($die)
			die();
	}

	/**
	 * Returns a json response and dies.
	 * @param $data
	 */
	function json_response($data)
	{
		status_header( 200 );
		header( 'Content-type: application/json; charset=UTF-8' );
		echo json_encode($data,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		die;
	}

	function gen_uuid($len = 8, $salt = "yourSaltHere") {
		$hex = md5($salt . uniqid("", true));

		$pack = pack('H*', $hex);
		$tmp = base64_encode($pack);

		$uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);

		$len = max(4, min(128, $len));

		while (strlen($uid) < $len)
			$uid .= gen_uuid(22);

		return substr($uid, 0, $len);
	}

	function parse_req($var,$default=null)
	{
		if (isset($_POST[$var]))
			return $_POST[$var];

		if (isset($_GET[$var]))
			return $_GET[$var];

		return $default;
	}

	/**
	 * Fetches a value from an array using a path string, eg 'some/setting/here'.
	 * @param $array
	 * @param $path
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	function arrayPath($array, $path, $defaultValue = null)	{
		$pathArray = explode('/', $path);

		$config = $array;

		for ($i = 0; $i < count($pathArray); $i++) {
			$part = $pathArray[$i];

			if (! isset($config[$part])) {
				return $defaultValue;
			}

			if ($i == count($pathArray) - 1) {
				return $config[$part];
			}

			$config = $config[$part];
		}

		return $defaultValue;
	}


	function htmlAttributes($attributes) {
		if (empty($attributes)) {
			return '';
		}

		$flattened = [];
		foreach($attributes as $key => $value) {
			if (strpos($value, "'") !== false) {
				$flattened[] = 'data-'.$key.'="'.$value.'"';
			} else {
				$flattened[] = "data-$key='$value'";
			}
		}

		return implode(' ', $flattened);
	}



	/**
	 * Unsets a deep value in multi-dimensional array based on a path string, eg 'some/deep/array/value'.
	 * @param $array
	 * @param $path
	 */
	function unsetArrayPath(&$array, $path)
	{
		$pathArray = explode('/', $path);

		$config = &$array;

		for ($i = 0; $i < count($pathArray); $i++) {
			$part = $pathArray[$i];

			if (! isset($config[$part])) {
				return;
			}

			if ($i == count($pathArray) - 1) {
				unset($config[$part]);
			}

			$config = &$config[$part];
		}
	}


	/**
	 * Fetches a value from an object or array using a path, eg 'some/setting/name'
	 *
	 * @param array|object $object
	 * @param string $path
	 * @param mixed|null $default
	 *
	 * @return mixed|null
	 */
	function objectPath($object, $path, $default = null) {
		$pathArray = explode('/', $path);

		$currentObject = $object;
		for($i = 0; $i < count($pathArray); $i++) {
			$part = $pathArray[$i];

			if (is_object($currentObject)) {
				if (!property_exists($currentObject, $part)) {
					return $default;
				}

				if ($i === count($pathArray) - 1) {
					return $currentObject->{$part};
				}

				$currentObject = $currentObject->{$part};
			} else {
				if (!isset($currentObject[$part])) {
					return $default;
				}

				if ($i === count($pathArray) - 1) {
					return $currentObject[$part];
				}

				$currentObject = $currentObject[$part];
			}
		}

		return $default;
	}

	/**
	 * Determines if the string starts with any of the supplied strings
	 *
	 * @param $haystack
	 * @param $needles
	 * @return bool
	 */
	function stringStartsWithAny($haystack, $needles) {
		foreach($needles as $needle) {
			if (strpos($haystack, $needle) === 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if the string contains any of the supplied strings
	 *
	 * @param $haystack
	 * @param $needles
	 * @return bool
	 */
	function stringContainsAny($haystack, $needles) {
		foreach($needles as $needle) {
			if (strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Insures all items are not null
	 * @param array $set
	 *
	 * @return bool
	 */
	function anyNull(...$set) {
		foreach($set as $item) {
			if($item === null) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Insures all items are not empty
	 * @param array $set
	 *
	 * @return bool
	 */
	function anyEmpty(...$set) {
		foreach($set as $item) {
			if(empty($item)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Insures all items are set
	 *
	 * @param array $array
	 * @param array $set
	 *
	 * @return bool
	 */
	function anyIsSet($array,...$set) {
		foreach($set as $item) {
			if (isset($array[$item])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Insures all items are set

	 * @param array $array
	 * @param array $set
	 *
	 * @return bool
	 */
	function allIsSet($array, ...$set) {
		foreach($set as $item) {
			if (!isset($array[$item])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if an array contains any of the values in another array
	 *
	 * @param $array
	 * @param $values
	 *
	 * @return bool
	 */
	function arrayContainsAny($array, $values) {
		foreach($values as $val) {
			if (in_array($val, $array)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if an array contains all of the values in another array
	 *
	 * @param $array
	 * @param $values
	 *
	 * @return bool
	 */
	function arrayContainsAll($array, $values) {
		foreach($values as $val) {
			if (!in_array($val, $array)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if an array is a keyed array
	 *
	 * @param array $arr
	 *
	 * @return bool
	 */
	function isKeyedArray($arr) {
		if (!is_array($arr) || empty($arr)) {
			return false;
		}

		return (array_keys($arr) !== range(0, count($arr) - 1));
	}

	/**
	 * Determines if a postIdExists
	 *
	 * @param $postId
	 *
	 * @return bool
	 */
	function postIdExists($postId) {
		return is_string(get_post_status($postId));
	}

	/**
	 * Returns the PHP memory limit in bytes.
	 * @return int
	 */
	function phpMemoryLimit($default = '64M') {
		$memory_limit = $default;

		if (function_exists('ini_get')) {
			$memory_limit = ini_get('memory_limit');
		} else {
			Logger::warning("ini_get disabled is disabled, unable to determine real memory limit", [], __FUNCTION__, __LINE__);
		}

		if (empty($memory_limit) || ($memory_limit == -1)) {
			$memory_limit = $default;
		}

		if (is_numeric($memory_limit)) {
			return $memory_limit;
		}

		preg_match('/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $memory_limit, $matches);
		$num = (float)$matches[1];
		switch (strtoupper($matches[2])) {
			case 'E':
				$num = $num * 1024;
			case 'P':
				$num = $num * 1024;
			case 'T':
				$num = $num * 1024;
			case 'G':
				$num = $num * 1024;
			case 'M':
				$num = $num * 1024;
			case 'K':
				$num = $num * 1024;
		}

		return intval($num);
	}

	/**
	 * Determines the mime type based on the metadata for an attachment
	 *
	 * @param $meta
	 *
	 * @return string|null
	 */
	function typeFromMeta($meta) {
		if (isset($meta['sizes']) || isset($meta['image_meta'])) {
			return 'image';
		}

		if (isset($meta['type'])) {
			$typeParts = explode('/', $meta['type']);
			return $typeParts[0];
		}

		if (isset($meta['mime-type'])) {
			$typeParts = explode('/', $meta['mime-type']);
			return $typeParts[0];
		}

		if (isset($meta['s3']) && isset($meta['s3']['mime-type'])) {
			$typeParts = explode('/', $meta['s3']['mime-type']);
			return $typeParts[0];
		}

		return null;
	}

	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|string[]  $needles
	 * @return bool
	 */
	function strEndsWith($haystack, $needles) {
		foreach ((array) $needles as $needle) {
			if ($needle !== '' && substr($haystack, -strlen($needle)) === (string) $needle) {
				return true;
			}
		}

		return false;
	}

	function discoverHooks($hooks) {
		global $wp_filter;

		$time = microtime(true);

		$foundHooks = [];
		$hashes = [];
		$themeDir = get_theme_file_path();

		foreach($hooks as $hookName) {
			if (!isset($wp_filter[$hookName])) {
				continue;
			}

			$wpHook = $wp_filter[$hookName];
			foreach($wpHook->callbacks as $priority => $hooks) {
				foreach($hooks as $hook => $hookData) {
					if (is_array($hookData['function']) && is_object($hookData['function'][0])) {
						if (strpos(get_class($hookData['function'][0]), 'MediaCloud') === 0) {
							continue;
						}
					}

					$line = 0;
					$type = 'unknown';
					$hookCallable = null;
					$filename = null;
					if (is_array($hookData['function'])) {
						$type = 'array';
						$hookCallable = [
							'static' => is_string($hookData['function'][0]),
							'class' => (is_string($hookData['function'][0])) ? $hookData['function'][0] : get_class($hookData['function'][0]),
							'method' => $hookData['function'][1]
						];

						try {
							$ref = new \ReflectionClass($hookData['function'][0]);
							$filename = $ref->getFileName();
							$line = $ref->getStartLine();
						} catch (\Exception $ex) {
							Logger::error("Error discovering hook: ".$ex->getMessage(), [], __METHOD__, __LINE__);
						}
					} else if (is_string($hookData['function'])) {
						$type = 'function';
						$hookCallable = [
							'function' => $hookData['function']
						];

						try {
							$ref = new \ReflectionFunction($hookData['function']);
							$filename = $ref->getFileName();
							$line = $ref->getStartLine();
						} catch (\Exception $ex) {
							Logger::error("Error discovering hook: ".$ex->getMessage(), [], __METHOD__, __LINE__);
						}
					} else if ($hookData['function'] instanceof \Closure) {
						$type = 'closure';
						$hookCallable = [];

						try {
							$ref = new \ReflectionFunction($hookData['function']);
							$line = $ref->getStartLine();
							$filename = $ref->getFileName();
							if ($ref->isClosure() && !empty($ref->getClosureThis())) {
								if (strpos(get_class($ref->getClosureThis()), 'MediaCloud') === 0) {
									continue;
								}
							}
						} catch (\Exception $ex) {
							Logger::error("Error discovering hook: ".$ex->getMessage(), [], __METHOD__, __LINE__);
						}
					}

					if (!empty($filename)) {
						if (strpos($filename, WP_PLUGIN_DIR) !== false) {
							$filename = ltrim(str_replace(WP_PLUGIN_DIR, '', $filename), '/');
							$filenameParts = explode('/', $filename);
							$pluginFolder = array_shift($filenameParts);

							$plugins = get_plugins('/'.$pluginFolder);
							if (count($plugins) > 0) {
								$plugin = array_values($plugins)[0];

								$hash = md5(serialize([
									'hook' => $hookName,
									'priority' => $priority,
									'type' => 'plugin',
									'plugin' => $pluginFolder,
									'callableType' => $type,
									'callable' => $hookCallable,
								]));

								if (!in_array($hash, $hashes)) {
									$hashes[] = $hash;

									$foundHooks[] = [
										'hook' => $hookName,
										'priority' => $priority,
										'type' => 'plugin',
										'plugin' => $pluginFolder,
										'name' => $plugin['Name'],
										'callableType' => $type,
										'callable' => $hookCallable,
										'realCallable' => $hookData['function'],
										'basename' => pathinfo('/'.$filename, PATHINFO_BASENAME),
										'filename' => '/'.$filename,
										'line' => $line,
										'hash' => $hash
									];
								}
							}
						} else if (strpos($filename, $themeDir) !== false) {
							$themeInfo = wp_get_theme();
							$filename = ltrim(str_replace($themeDir, '', $filename), '/');
							$filenameParts = explode('/', $filename);
							$themeFolder = array_shift($filenameParts);

							$hash = md5(serialize([
								'hook' => $hookName,
								'priority' => $priority,
								'type' => 'plugin',
								'plugin' => $themeFolder,
								'callableType' => $type,
								'callable' => $hookCallable,
							]));

							if (!in_array($hash, $hashes)) {
								$hashes[] = $hash;

								$foundHooks[] = [
									'hook' => $hookName,
									'priority' => $priority,
									'type' => 'theme',
									'plugin' => $themeFolder,
									'name' => $themeInfo->get('Name'),
									'callableType' => $type,
									'callable' => $hookCallable,
									'realCallable' => $hookData['function'],
									'basename' => pathinfo('/'.$filename, PATHINFO_BASENAME),
									'filename' => '/'.$filename,
									'line' => $line,
									'hash' => $hash
								];
							}
						}
					}
				}
			}
		}

		return $foundHooks;
	}

	/**
	 * Disables non-Media Cloud hooks and filters for the specified action/filter names
	 *
	 * @param string[] $hooks
	 */
	function disableHooks($hooks) {
		global $wp_filter;

		foreach($hooks as $hookName) {
			if (!isset($wp_filter[$hookName])) {
				continue;
			}

			$wpHook = $wp_filter[$hookName];
			foreach($wpHook->callbacks as $priority => $hooks) {
				foreach($hooks as $hook => $hookData) {
					if (is_array($hookData['function']) && is_object($hookData['function'][0])) {
						if (strpos(get_class($hookData['function'][0]), 'MediaCloud') === 0) {
							continue;
						}
					}

					$wpHook->remove_filter($hook, $hookData['function'], $priority);
				}
			}
		}
	}

	/**
	 * Wrapper for set_time_limit to see if it is enabled.
	 *
	 * @since 2.6.0
	 * @param int $limit Time limit.
	 */
	function ilab_set_time_limit( $limit = 0 ) {
		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( $limit ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Safely finds an executable
	 *
	 * @param $executableName
	 * @param string $versionSwitch
	 *
	 * @return false|mixed|string
	 */
	function ilab_find_executable($executableName, $versionSwitch = '') {
		if (!function_exists( 'shell_exec') || (false !== strpos(ini_get( 'disable_functions' ), 'shell_exec')) || ini_get('safe_mode')) {
			return false;
		}

		$allowed = ini_get('open_basedir');
		$allowedDirs = explode(':', $allowed);
		$foundExe = false;
		foreach($allowedDirs as $allowedDir) {
			$exe = trailingslashit($allowedDir).$executableName;
			if (file_exists($exe)) {
				$foundExe = $exe;
				break;
			}
		}

		if ($foundExe === false) {
			$testExe = trim("$executableName $versionSwitch");
			$result = shell_exec('/usr/local/bin/'.$testExe);
			if (!empty($result)) {
				$foundExe = '/usr/local/bin/'.$executableName;
			}
		}

		return $foundExe;
	}
}

