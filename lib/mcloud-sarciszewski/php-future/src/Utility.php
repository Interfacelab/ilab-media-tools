<?php

namespace MediaCloud\Vendor\Sarciszewski\PHPFuture;

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Scott Arciszewski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
class Utility extends BaseFuture
{
    /**
     * Return the values from a single column in the input array
     *
     * @param array $array A multi-dimensional array (record set) from which to pull a column of values.
     * @param string|int $column_key The column of values to return.
     * @param string|int|null $index_key The column to use as the index/keys for the returned array.
     *
     * @return array
     */
    public static function arrayColumn(array $array, $column_key, $index_key = null)
    {
        $aReturn = array();
        if ($column_key === null) {
            // No column key? Grab the whole row...
            if ($index_key === null) {
                return $array;
            }
            foreach ($array as $sub) {
                $aReturn[$sub[$index_key]] = $sub;
            }
        } elseif (empty($index_key)) {
            foreach ($array as $sub) {
                $aReturn[] = $sub[$column_key];
            }
        } else {
            foreach ($array as $sub) {
                $aReturn[$sub[$index_key]] = $sub[$column_key];
            }
        }
        return $aReturn;
    }

    /**
     * Convert a hexadecimal string into raw binary
     *
     * @param string $data Hexadecimal data
     *
     * @return string
     */
    public static function hexToBin($data)
    {
        if (self::ourStrlen($data) % 2 !== 0) {
            \trigger_error("hex2bin(): Hexadecimal input string must have an even length", E_USER_WARNING);
            return false;
        }
        return \pack('H*', $data);
    }
}
