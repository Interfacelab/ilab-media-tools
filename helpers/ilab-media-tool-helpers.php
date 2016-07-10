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

///**
// * Brute force debug tool
// * @param $what
// * @param bool|true $die
// */
//function vomit($what, $die=true)
//{
//    echo "<pre>";
//    print_r($what);
//    echo "</pre>";
//
//    if ($die)
//        die();
//}

/**
 * Returns a json response and dies.
 * @param $data
 */
function json_response($data)
{
    status_header( 200 );
    header( 'Content-type: application/json; charset=UTF-8' );
    echo json_encode($data,JSON_PRETTY_PRINT);
    die;
}

function gen_uuid($len = 8)
{

    $hex = md5("yourSaltHere" . uniqid("", true));

    $pack = pack('H*', $hex);
    $tmp = base64_encode($pack);

    $uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);

    $len = max(4, min(128, $len));

    while (strlen($uid) < $len)
        $uid .= gen_uuid(22);

    return substr($uid, 0, $len);
}

function ejson_decode_file($fileName,$assoc_array=false, $depth = 512, $options = 0) {
    $dir=dirname($fileName);
    $contents=preg_replace('#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#','',file_get_contents($fileName));

    $includeMatches=[];
    if (preg_match_all('#{%\s*include\s+([/aA-zZ0-9-_.]+)\s*%}#',$contents,$includeMatches,PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))
    {
        for($i=count($includeMatches[0])-1; $i>=0; $i--)
        {
            $included=file_get_contents($dir.'/'.$includeMatches[1][$i][0]);
            $contents=substr_replace($contents,$included,$includeMatches[0][$i][1],strlen($includeMatches[0][$i][0]));
        }
    }

    return json_decode($contents, $assoc_array, $depth, $options);
}

function parse_req($var,$default=null)
{
    if (isset($_POST[$var]))
        return $_POST[$var];

    if (isset($_GET[$var]))
        return $_GET[$var];

    return $default;
}