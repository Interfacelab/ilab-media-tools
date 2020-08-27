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
class Security extends BaseFuture
{
    /**
     * Equivalent to hash_equals() in PHP 5.6 
     * 
     * @param string $knownString
     * @param string $userString
     * 
     * @return bool
     */
    public static function hashEquals($knownString, $userString)
    {
        // We have to roll our own
        $kLen = self::ourStrlen($knownString);
        $uLen = self::ourStrlen($userString);
        if ($kLen !== $uLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $kLen; $i++) {
            $result |= (ord($knownString[$i]) ^ ord($userString[$i]));
        }
        // They are only identical strings if $result is exactly 0...
        return 0 === $result;
    }
    
    /**
     * Password Based Key Derivation Function #2
     *
     * Underlying feature for hash_pbkdf2() and openssl_pbkdf2() for PHP < 5.5
     *
     * @param string $algo
     * @param string $password
     * @param string $salt
     * @param int $iterations
     * @param int $length
     * 
     * @return string
     */
    public static function pbkdf2($algo, $password, $salt, $iterations, $length = 0)
    {
        if (!\in_array($algo, \hash_algos(), true)) {
            throw new \Exception('PBKDF2 ERROR: Invalid hash algorithm.');
        }

        $hashlen = self::ourStrlen(\hash($algo, "", true));
        if ($hashlen === 0) {
            return '';
        }

        $block_count = \ceil($length / $hashlen);

        $output = '';
        for ($i = 1; $i <= $block_count; ++$i) {
            $last = $salt.\pack('N', $i);
            $xorsum = \hash_hmac($algo, $last, $password, true);
            $last = $xorsum;
            for ($j = 1; $j < $iterations; ++$j) {
                $xorsum ^= ($last = \hash_hmac($algo, $last, $password, true));
            }
            $output .= $xorsum;
        }
        return self::ourSubstr($output, 0, $length);
    }
}
