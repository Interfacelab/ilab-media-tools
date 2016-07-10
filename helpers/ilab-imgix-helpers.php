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