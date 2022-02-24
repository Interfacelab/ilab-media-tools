<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 04.02.2020
 * Time: 18:00
 */

// Set up the API Key. \MediaCloud\Vendor\ShortPixel\setKey("YOUR_API_KEY");

// Compress with default settings \MediaCloud\Vendor\ShortPixel\fromUrls("https://your.site/img/unoptimized.png")->toFiles("/path/to/save/to");
// Compress with default settings but specifying a different file name \MediaCloud\Vendor\ShortPixel\fromUrls("https://your.site/img/unoptimized.png")->toFiles("/path/to/save/to", "optimized.png");

// Compress with default settings from a local file \MediaCloud\Vendor\ShortPixel\fromFile("/path/to/your/local/unoptimized.png")->toFiles("/path/to/save/to");
// Compress with default settings from several local files \MediaCloud\Vendor\ShortPixel\fromFiles(array("/path/to/your/local/unoptimized1.png", "/path/to/your/local/unoptimized2.png"))->toFiles("/path/to/save/to");
//Compres and rename each file \MediaCloud\Vendor\ShortPixel\fromFiles(array("/path/to/your/local/unoptimized1.png", "/path/to/your/local/unoptimized2.png"))->toFiles("/path/to/save/to", ['renamed-one.png', 'renamed-two.png']);

// Compress with a specific compression level: 0 - lossless, 1 - lossy (default), 2 - glossy \MediaCloud\Vendor\ShortPixel\fromFile("/path/to/your/local/unoptimized.png")->optimize(2)->toFiles("/path/to/save/to");

// Compress and resize - image is resized to have the either width equal to specified or height equal to specified
//   but not LESS (with settings below, a 300x200 image will be resized to 150x100) \MediaCloud\Vendor\ShortPixel\fromUrls("https://your.site/img/unoptimized.png")->resize(100, 100)->toFiles("/path/to/save/to");
// Compress and resize - have the either width equal to specified or height equal to specified
//   but not MORE (with settings below, a 300x200 image will be resized to 100x66) \MediaCloud\Vendor\ShortPixel\fromUrls("https://your.site/img/unoptimized.png")->resize(100, 100, true)->toFiles("/path/to/save/to");

// Keep the exif when compressing \MediaCloud\Vendor\ShortPixel\fromUrls("https://your.site/img/unoptimized.png")->keepExif()->toFiles("/path/to/save/to");

// Also generate and save a WebP version of the file - the WebP file will be saved next to the optimized file, with  same basename and .webp extension \MediaCloud\Vendor\ShortPixel\fromUrls("https://your.site/img/unoptimized.png")->generateWebP()->toFiles("/path/to/save/to");

//Compress from a folder - the status of the compressed images is saved in a text file named .shortpixel in each image folder \MediaCloud\Vendor\ShortPixel\ShortPixel::setOptions(array("persist_type" => "text"));
//Each call will optimize up to 10 images from the specified folder and mark in the .shortpixel file.
//It automatically recurses a subfolder when finds it
//Save to the same folder, set wait time to 300 to allow enough time for the images to be processed
$ret = \MediaCloud\Vendor\ShortPixel\fromFolder("/path/to/your/local/folder")->wait(300)->toFiles("/path/to/your/local/folder");
//Save to a different folder. CURRENT LIMITATION: When using the text persist type and saving to a different folder, you also need to specify the destination folder as the fourth parameter to fromFolder ( it indicates where the persistence files should be created)
$ret = \MediaCloud\Vendor\ShortPixel\fromFolder("/path/to/your/local/folder", 0, array(), "/different/path/to/save/to")->wait(300)->toFiles("/different/path/to/save/to");
//use a URL to map the folder to a WEB path in order for our servers to download themselves the images instead of receiving them via POST - faster and less exposed to connection timeouts
$ret = \MediaCloud\Vendor\ShortPixel\fromWebFolder("/path/to/your/local/folder", "http://web.path/to/your/local/folder")->wait(300)->toFiles("/path/to/save/to");
//let ShortPixel back-up all your files, before overwriting them (third parameter of toFiles).
$ret = \MediaCloud\Vendor\ShortPixel\fromFolder("/path/to/your/local/folder")->wait(300)->toFiles("/path/to/save/to", null, "/back-up/path");
//Recurse only $N levels down into the subfolders of the folder ( N == 0 means do not recurse )
$ret = \MediaCloud\Vendor\ShortPixel\fromFolder("/path/to/your/local/folder", 0, array(), false, ShortPixel::CLIENT_MAX_BODY_SIZE, $N)->wait(300)->toFiles("/path/to/save/to");

//Set custom cURL options (proxy) \MediaCloud\Vendor\ShortPixel\setCurlOptions(array(CURLOPT_PROXY => '66.96.200.39:80', CURLOPT_REFERER => 'https://shortpixel.com/'));


//A simple loop to optimize all images from a folder
$stop = false;
while(!$stop) {
    $ret = \MediaCloud\Vendor\ShortPixel\fromFolder("/path/to/your/local/folder")->wait(300)->toFiles("/path/to/save/to");
    if(count($ret->succeeded) + count($ret->failed) + count($ret->same) + count($ret->pending) == 0) {
        $stop = true;
    }
}

//Compress from an image in memory
$myImage = file_get_contents($pathTo_shortpixel.png);
$ret = \MediaCloud\Vendor\ShortPixel\fromBuffer('shortpixel.png', $myImage)->wait(300)->toFiles(self::$tempDir);

//Get account status and credits info:
$ret = \MediaCloud\Vendor\ShortPixel\ShortPixel::getClient()->apiStatus(YOUR_API_KEY);

