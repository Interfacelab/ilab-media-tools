<?php
/*
function __autoload($class_name) {
    require_once("./$class_name.php");
}
use MediaCloud\Vendor\ShortPixel\Persister;
use MediaCloud\Vendor\ShortPixel\Commander;
use MediaCloud\Vendor\ShortPixel\Client;
use MediaCloud\Vendor\ShortPixel\Exception;
use MediaCloud\Vendor\ShortPixel\Source;
use MediaCloud\Vendor\ShortPixel\persist\TextPersister;
use MediaCloud\Vendor\ShortPixel\persist\ExifPersister;
use MediaCloud\Vendor\ShortPixel\persist\PNGMetadataExtractor;
use MediaCloud\Vendor\ShortPixel\persist\PNGReader;
use MediaCloud\Vendor\ShortPixel\Result;
use MediaCloud\Vendor\ShortPixel;
*/

require_once("ShortPixel/Settings.php");
require_once("ShortPixel/Lock.php");
require_once("ShortPixel/SPLog.php");
require_once("ShortPixel/SPCache.php");
require_once("ShortPixel/SPTools.php");

require_once("ShortPixel/Persister.php");
require_once("ShortPixel/persist/TextPersister.php");
require_once("ShortPixel/persist/ExifPersister.php");
require_once("ShortPixel/persist/TextMetaFile.php");
require_once("ShortPixel/persist/PNGMetadataExtractor.php");
require_once("ShortPixel/persist/PNGReader.php");

require_once("ShortPixel/notify/ProgressNotifier.php");
require_once("ShortPixel/notify/ProgressNotifierMemcache.php");
require_once("ShortPixel/notify/ProgressNotifierFileQ.php");

require_once("ShortPixel/Commander.php");
require_once("ShortPixel/Client.php");
require_once("ShortPixel/Exception.php");
require_once("ShortPixel/Source.php");
require_once("ShortPixel/Result.php");
require_once("ShortPixel.php");

