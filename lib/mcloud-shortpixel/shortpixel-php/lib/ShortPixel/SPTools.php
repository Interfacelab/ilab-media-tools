<?php
/**
 * User: simon
 * Date: 13.04.2021
 */

namespace MediaCloud\Vendor\ShortPixel;


class SPTools {
    public static function trailingslashit($path) {
        return rtrim($path, '/') . '/';
    }
}