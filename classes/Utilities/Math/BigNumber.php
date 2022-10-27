<?php

/**
 * # The MIT License (MIT)
 *
 * Copyright (c) 2016 Matthew Allan matthew.james.allan@gmail.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

namespace MediaCloud\Plugin\Utilities\Math;

class BigNumber
{
    /**
     * @var string
     */
    private $characteristic = '0';

    /**
     * @var int
     */
    private $characteristicLength;

    /**
     * @var string
     */
    private $mantissa = '';

    /**
     * @var boolean
     */
    private $isNegative = false;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        if (static::isNumeric($value)) {
            $this->setValues($value);
        }
    }

    /**
     * @return boolean
     */
    public function isNegative()
    {
        return $this->isNegative;
    }

    /**
     * @return boolean
     */
    public function isPositive()
    {
        return !$this->isNegative();
    }

    /**
     * @return boolean
     */
    public function isZero()
    {
        return $this->getCharacteristic() === '0' && $this->getMantissa() === '';
    }

    /**
     * @return string
     */
    public function getCharacteristic()
    {
        return $this->characteristic;
    }

    /**
     * @return int
     */
    public function getCharacteristicLength()
    {
        if (is_null($this->characteristicLength)) {
            $this->characteristicLength = strlen($this->getCharacteristic());
        }

        return $this->characteristicLength;
    }

    /**
     * @return string
     */
    public function getMantissa()
    {
        return $this->mantissa;
    }

    /**
     * @param string $value
     */
    private function setValues($value)
    {
        if ($value[0] === '-') {
            $this->isNegative = true;
            $value            = substr($value, 1);
        }

        $this->characteristic = static::parseCharacteristic($value);
        $this->mantissa       = static::parseMantissa($value);
    }

    /**
     * @param  string $value
     * @return string
     */
    private static function parseCharacteristic($value)
    {
        if (strpos($value, '.') !== false) {
            $value = substr($value, 0, strpos($value, '.'));
        }

        return strlen($value) === 1 ? $value : ltrim($value, '0');
    }

    /**
     * @param  string $value
     * @return string
     */
    private static function parseMantissa($value)
    {
        if (($separatorPos = strrpos($value, '.')) === false) {
            return '';
        }

        $value = substr($value, strrpos($value, '.') + 1);

        return rtrim($value, '0');
    }

    /**
     * @param  string $value
     * @return boolean
     */
    private static function isNumeric($value)
    {
        // remove the last decimal separator only.
        // If it has more decimal separators it's invalid.
        $separatorPos = strrpos($value, '.');
        if ($separatorPos !== false) {
            $value = substr_replace($value, '', $separatorPos, 1);
        }

        return ctype_digit($value) || ($value[0] === '-' && ctype_digit(substr($value, 1)));
    }
}
