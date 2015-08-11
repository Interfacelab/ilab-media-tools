<?php

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

function sizeToFillSize($innerWidth, $innerHeight, $outerWidth, $outerHeight)
{
    if ($innerWidth <= 0 || $innerHeight <= 0)
        return [$outerWidth, $outerHeight];

    $ratioW = $outerWidth / $innerWidth;
    $ratioH = $outerHeight / $innerHeight;

    if ($ratioW > $ratioH)
        return [$outerWidth, round($innerHeight * $ratioW)];
    else
        return [round($innerWidth * $ratioH),$outerHeight];

    return [$outerWidth, $outerHeight];
}