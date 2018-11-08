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

namespace ILAB\MediaCloud\Utilities {
	// As this file is automatically included if loaded through autoloader
	// do a check and avoid the direct access guard in that case.
	if (!defined('ABSPATH') && empty($GLOBALS['__composer_autoload_files'])) { header('Location: /'); die; }

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
		echo json_encode($data,JSON_PRETTY_PRINT);
		die;
	}

	function gen_uuid($len = 8)
	{

		$hex = md5("yourSaltHere" . uniqid("", true));

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
}

