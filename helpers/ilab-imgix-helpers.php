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

function imgixCurrentValue($param,$current,$default)
{
    if (isset($current[$param]))
        return $current[$param];

    return $default;
}

function imgixCurrentColorValue($param,$current,$default)
{
    if (isset($current[$param]))
    {
        $val=$current[$param];
        $val=str_replace('#','',$val);
        if (strlen($val)==8)
            return '#'.substr($val,2);

        return '#'.$val;
    }

    return $default;
}

function imgixCurrentAlphaValue($param,$current,$default)
{
    if (isset($current[$param]))
    {
        $val=$current[$param];
        $val=str_replace('#','',$val);
        if (strlen($val)==8)
        {
            $alpha=substr($val,0,2);
            $alpha=round((hexdec($alpha)/255.0)*100.0);
            return $alpha;
        }
    }

    return $default;
}

function imgixCurrentMediaSrcValue($param,$current)
{
    if (isset($current[$param.'_url']))
    {
        return $current[$param.'_url'];
    }

    return '';
}

function imgixIsSelected($param,$current,$value,$defaultValue,$selectedOutput,$unselectedOutput='')
{
    if (isset($current[$param]))
    {
        if ($current[$param]==$value)
            return $selectedOutput;

        return $unselectedOutput;
    }

    if ($defaultValue==$value)
        return $selectedOutput;

    return $unselectedOutput;
}


function imgixAutoIsSelected($value,$current,$selectedOutput,$unselectedOutput='')
{
    if (isset($current['auto']))
    {
        $parts=explode(',',$current['auto']);
        foreach($parts as $part)
            if ($part==$value)
                return $selectedOutput;
    }

    return $unselectedOutput;
}

function imgixModeIsSelected($current,$mode,$selectedOutput,$unselectedOutput='')
{
    if (isset($current['auto']))
    {
        $modes=explode(',',$current['auto']);
        foreach($modes as $amode)
            if ($amode==$mode)
                return $selectedOutput;
    }

    return $unselectedOutput;
}

/**
 * Register a new image size with additional imgix parameters to use for that size.  For imgix parameters, see
 * their API docs at: https://docs.imgix.com/apis/url
 *
 * Cropping behavior for the image size is dependent on the value of $crop:
 * 1. If false (default), images will be scaled, not cropped.
 * 2. If an array in the form of array( x_crop_position, y_crop_position ):
 *    - x_crop_position accepts 'left' 'center', or 'right'.
 *    - y_crop_position accepts 'top', 'center', or 'bottom'.
 *    Images will be cropped to the specified dimensions within the defined crop area.
 * 3. If true, images will be cropped to the specified dimensions using center positions.
 *
 * @since 2.9.0
 *
 * @global array $_wp_additional_image_sizes Associative array of additional image sizes.
 *
 * @param string     $name        Image size identifier.
 * @param int        $width       Image width in pixels.
 * @param int        $height      Image height in pixels.
 * @param bool|array $crop        Optional. Whether to crop images to specified width and height or resize.
 *                                An array can specify positioning of the crop area. Default false.
 * @param array      $imgixParams   Optional. Whether to crop images to specified width and height or resize.
 *                                  An array can specify positioning of the crop area. Default false.
 */
function addImgixImageSize( $name, $width = 0, $height = 0, $crop = false, $imgixParams = null) {
    global $_wp_additional_image_sizes;

    $_wp_additional_image_sizes[ $name ] = array(
        'width'  => absint( $width ),
        'height' => absint( $height ),
        'crop'   => $crop,
        'imgix' => $imgixParams
    );
}