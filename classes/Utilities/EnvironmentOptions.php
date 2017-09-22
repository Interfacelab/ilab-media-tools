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

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class EnvironmentOptions
 * @package ILAB\MediaCloud\Utilities
 */
final class EnvironmentOptions {
	/**
	 * Fetches an option from WordPress or the environment.
	 *
	 * @param string|null $optionName
	 * @param string|array|null $envVariableName
	 * @param bool $default
	 *
	 * @return array|false|mixed|string|null
	 */
	public static function Option($optionName = null, $envVariableName = null, $default = false) {
		if (empty($optionName) && empty($envVariableName)) {
			return $default;
		}

		if ($envVariableName == null) {
			$envVariableName = str_replace('-','_', strtoupper($optionName));
		}

		if (is_array($envVariableName)) {
			foreach($envVariableName as $envVariable) {
				$envval = getenv($envVariable);
				if ($envval) {
					return $envval;
				}
			}
		} else {
			$envval = getenv($envVariableName);
			if ($envval) {
				return $envval;
			}
		}

		if (empty($optionName)) {
			return $default;
		}

		return get_option($optionName, $default);
	}
}