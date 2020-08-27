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
class Image extends BaseFuture
{
    /**
     * Get the size of an image from a string
     *
     * @param string $imagedata - Binary image data
     * @param &array $imageinfo - Reference to a metadata array
     *
     * @return array
     */
    public static function imageSizeFromString($imagedata, &$imageinfo = null)
    {
        $uri = 'data://application/octet-stream;base64,'.\base64_encode($imagedata);
        if ($imageinfo !== null) {
            return \getimagesize($uri, $imageinfo);
        }
        return \getimagesize($uri);
    }
}
