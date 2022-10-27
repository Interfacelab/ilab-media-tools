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

if (!defined('ABSPATH')) { header('Location: /'); die; }

function sizeToFitSize($innerWidth, $innerHeight, $outerWidth, $outerHeight)  {
	if ($innerWidth <= 0 || $innerHeight <= 0) {
		return [$outerWidth, $outerHeight];
	}

	if ($outerWidth <= 0 && $outerHeight <= 0) {
		return [0, 0];
	}

	if ($outerWidth <= 0) {
		if ($innerHeight <= $outerHeight) {
			return [$innerWidth, $innerHeight];
		}

		$ratio = $outerHeight / $innerHeight;

		return [intval(round($innerWidth * $ratio)), intval($outerHeight)];
	}

	if ($outerHeight <= 0) {
		if ($innerWidth <= $outerWidth) {
			return [$innerWidth, $innerHeight];
		}

		$ratio = $outerWidth / $innerWidth;

		return [intval($outerWidth), intval(round($innerHeight * $ratio))];
	}

    $ratioW = $outerWidth / $innerWidth;
    $ratioH = $outerHeight / $innerHeight;

    if ($ratioW < $ratioH) {
        return [intval($outerWidth), intval(round($innerHeight * $ratioW))];
    } else {
        return [intval(round($innerWidth * $ratioH)), intval($outerHeight)];
    }
}

function sizeToFillSize($innerWidth, $innerHeight, $outerWidth, $outerHeight, $preserveHeight=true) {
    if ($innerWidth <= 0 || $innerHeight <= 0) {
        return [$outerWidth, $outerHeight];
    }

    $ratioW = $outerWidth / $innerWidth;
    $ratioH = $outerHeight / $innerHeight;

    if (($ratioW > $ratioH) && ($preserveHeight)) {
        return [intval($outerWidth), intval(round($innerHeight * $ratioW))];
    } else {
        return [intval(round($innerWidth * $ratioH)), intval($outerHeight)];
    }
}

function __gcd($a, $b) {
	return ($a % $b) ? __gcd($b, $a % $b) : $b;
}

function generateAspectRatio($width, $height) {
	$g = __gcd($width, $height);
	return [
		$width/$g,
		$height/$g
	];
}
