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

function sizeToFitSize($innerWidth, $innerHeight, $outerWidth, $outerHeight)
{
    if ($innerWidth <= 0 || $innerHeight <= 0)
        return [$outerWidth, $outerHeight];

    $ratioW = $outerWidth / $innerWidth;
    $ratioH = $outerHeight / $innerHeight;

    if ($ratioW < $ratioH)
        return [$outerWidth, round($innerHeight * $ratioW)];
    else
        return [round($innerWidth * $ratioH),$outerHeight];

    return [$outerWidth, $outerHeight];
}

function sizeToFillSize($innerWidth, $innerHeight, $outerWidth, $outerHeight, $preserveHeight=true)
{
    if ($innerWidth <= 0 || $innerHeight <= 0)
        return [$outerWidth, $outerHeight];

    $ratioW = $outerWidth / $innerWidth;
    $ratioH = $outerHeight / $innerHeight;

    if (($ratioW > $ratioH) && ($preserveHeight))
        return [$outerWidth, round($innerHeight * $ratioW)];
    else
        return [round($innerWidth * $ratioH),$outerHeight];

    return [$outerWidth, $outerHeight];
}