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
			Logger::info("ini_get disabled is disabled, unable to determine real memory limit", [], __FUNCTION__, __LINE__);
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
}

