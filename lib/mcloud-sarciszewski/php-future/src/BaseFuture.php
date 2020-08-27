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
class BaseFuture
{
    /**
     * Multi-byte-safe string length calculation
     *
     * @param string $str
     * @return int
     */
    protected static function ourStrlen($str)
    {
        // Premature optimization: cache the function_exists() result
        static $exists = null;
        if ($exists === null) {
            $exists = \function_exists('\\mb_strlen');
        }

        // If it exists, we need to make sure we're using 8bit mode
        if ($exists) {
            return \mb_strlen($str, '8bit');
        }
        return \strlen($str);
    }

    /**
     * Multi-byte-safe substring calculation
     *
     * @param string $str
     * @param int $start
     * @param int $length (optional)
     * @return string
     */
    protected static function ourSubstr($str, $start = 0, $length = null)
    {
        // Premature optimization: cache the function_exists() result
        static $exists = null;
        if ($exists === null) {
            $exists = \function_exists('\\mb_substr');
        }

        // If it exists, we need to make sure we're using 8bit mode
        if ($exists) {
            return \mb_substr($str, $start, $length, '8bit');
        } elseif ($length !== null) {
            return \substr($str, $start, $length);
        }
        return \substr($str, $start);
    }
}
