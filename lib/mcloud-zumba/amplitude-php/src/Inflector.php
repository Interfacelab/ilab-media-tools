<?php

namespace MediaCloud\Vendor\Zumba\Amplitude;

class Inflector
{
    /**
     * All inflections are cached here to prevent overhead from multiple calls for the same conversion.
     *
     * @var array
     */
    protected static $cache = array();

    /**
     * Stores and returns values from the cache.
     *
     * @param string $method The name of the method calling the cache
     * @param string $key The original value before conversion
     * @param string $value The converted value (used to set)
     * @return string The converted value
     */
    protected static function cache($method, $key, $value = null)
    {
        if (is_null($value)) {
            $value = isset(static::$cache[$method][$key]) ? static::$cache[$method][$key] : null;
        } else {
            static::$cache[$method][$key] = $value;
        }

        return $value;
    }

    /**
     * Convert someValue to some_value
     *
     * @param string $value A camelCasedString
     * @return string An underscored_string
     */
    public static function underscore($value = '')
    {
        $result = static::cache(__FUNCTION__, $value);
        if (!$result) {
            $result = strtolower(preg_replace('/([A-Z])/', '_\1', $value));
            static::cache(__FUNCTION__, $value, $result);
        }
        return $result;
    }

    /**
     * Convert some_value to someValue
     *
     * @param string $value An underscored_string
     * @access public
     * @return string A camelCased string
     * @static
     */
    public static function camelCase($value = '')
    {
        $result = static::cache(__FUNCTION__, $value);
        if (!$result) {
            $newValue = ucwords(str_replace('_', ' ', $value));
            $result = str_replace(' ', '', strtolower($newValue[0]) . substr($newValue, 1));
            static::cache(__FUNCTION__, $value, $result);
        }
        return $result;
    }
}
