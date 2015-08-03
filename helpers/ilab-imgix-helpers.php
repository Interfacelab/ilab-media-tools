<?php
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