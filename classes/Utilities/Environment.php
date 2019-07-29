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
final class Environment {
	private static $booted = false;
	private static $networkMode = false;

	/**
	 * Sets up the environment
	 */
	public static function Boot() {
		if (!static::$booted) {
			global $media_cloud_licensing;
			if ($media_cloud_licensing->is_plan('pro')) {
				static::$networkMode = get_site_option('mcloud-network-mode');
				if(!static::$networkMode) {
					static::$networkMode = static::Option('mcloud-network-mode', null, false);
				}
			}

			static::$booted = true;
		}
	}

	/**
	 * Determines if the plugin is running in network or single (per site) mode
	 * @return bool
	 */
	public static function NetworkMode() {
		global $media_cloud_licensing;
		if ($media_cloud_licensing->is_plan('pro')) {
			return static::$networkMode;
		} else {
			return false;
		}
	}

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

		if (empty($optionName)) {
			if (!is_array($envVariableName)) {
				$envVariableName = [$envVariableName];
			}
		} else {
			$optionEnvName = str_replace('-','_', strtoupper($optionName));
			if (is_array($envVariableName)) {
				$envVariableName = array_merge([$optionEnvName], $envVariableName);
			} else if (!empty($envVariableName)) {
				$envVariableName = [$optionEnvName, $envVariableName];
			} else {
				$envVariableName = [$optionEnvName];
			}
		}

		foreach($envVariableName as $envVariable) {
			if (defined($envVariable)) {
				$envval = constant($envVariable);
			} else {
				$envval = getenv($envVariable);
			}

			if ($envval !== false) {
				return $envval;
			}
		}

		if (empty($optionName)) {
			return $default;
		}

		if (static::$networkMode) {
			$val = get_site_option($optionName, $default);
		} else {
			$val = get_option($optionName, $default);
		}

		return $val;
	}

	/**
	 * Updates an option, automatically updating for network if in network mode
	 *
	 * @param $optionName
	 * @param $value
	 */
	public static function UpdateOption($optionName, $value) {
		if (static::$networkMode) {
			update_site_option($optionName, $value);
		} else {
			update_option($optionName, $value);
		}
	}

	/**
	 * Deletes an option, automatically updating for network if in network mode
	 * @param $optionName
	 */
	public static function DeleteOption($optionName) {
		if (static::$networkMode) {
			delete_site_option($optionName);
		} else {
			delete_option($optionName);
		}
	}

    /**
     * Transitions options from older versions of the plugin to the new option name
     *
     * @param $options
     */
	public static function TransitionOptions($options) {
        foreach($options as $fromOptionName => $toOptionName) {
            $val = static::Option($fromOptionName);
            if ($val !== null) {
                static::UpdateOption($toOptionName, $val);
//                static::DeleteOption($fromOptionName);
            }
        }
    }

    /**
     * Determines if any the following environment variables exist
     * @param $envVars
     * @return bool|array
     */
    public static function DeprecatedEnvironmentVariables($envVars) {
        $exist = [];

        foreach($envVars as $oldEndVar => $newEnvVar) {
            $val = getenv($oldEndVar);
            if ($val !== false) {
                $exist[$oldEndVar] = $newEnvVar;
            }
        }

        if (empty($exist)) {
            return false;
        }

        return $exist;
    }

    /**
     * Copies option values from one option to the other if the other is blank/empty/null
     *
     * @param $options
     */
    public static function CopyOptions($options) {
        foreach($options as $fromOption => $toOption) {
            $val = static::Option($fromOption);
            if ($val !== false) {
                $toVal = static::Option($toOption);
                if ($toVal === false) {
                    static::UpdateOption($toOption, $val);
                }
            }
        }
    }
}