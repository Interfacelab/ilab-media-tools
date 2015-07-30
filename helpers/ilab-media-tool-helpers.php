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

function json_response($data)
{
    status_header( 200 );
    header( 'Content-type: application/json; charset=UTF-8' );
    echo json_encode($data,JSON_PRETTY_PRINT);
    die;
}