<?php
require_once("../lib/shortpixel-php-req.php"); \MediaCloud\Vendor\ShortPixel\setKey("<YOUR API KEY HERE>");
$tmpFolder = tempnam(sys_get_temp_dir(), "shortpixel-php");
echo("Temp folder: " . $tmpFolder);
if(file_exists($tmpFolder)) unlink($tmpFolder);
mkdir($tmpFolder); \MediaCloud\Vendor\ShortPixel\fromUrls("https://shortpixel.com/img/tests/wrapper/shortpixel.png")->refresh()->wait(300)->toFiles($tmpFolder);
echo("\nSuccessfully saved the optimized image from URL to temp folder.\n"); \MediaCloud\Vendor\ShortPixel\fromFile(__DIR__ . "/data/cc.jpg")->refresh()->wait(300)->toFiles($tmpFolder);
echo("\nSuccessfully saved the optimized image from path to temp folder.\n\n");
