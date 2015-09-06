<?php

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