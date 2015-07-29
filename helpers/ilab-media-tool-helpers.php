<?php

/**
 * Brute force debug tool
 * @param $what
 * @param bool|true $die
 */
function vomit($what, $die=true)
{
    echo "<pre>";
    print_r($what);
    echo "</pre>";

    if ($die)
        die();
}