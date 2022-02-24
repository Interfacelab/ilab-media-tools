<?php

namespace MediaCloud\Vendor\ShortPixel;

class ShortPixel {
    const LIBRARY_CODE = "sp-sdk";
    const VERSION = "1.8.7";
    const DEBUG_LOG = false;

    const MAX_ALLOWED_FILES_PER_CALL = 10;
    const MAX_ALLOWED_FILES_PER_WEB_CALL = 30;
    const MAX_API_ALLOWED_FILES_PER_WEB_CALL = 150;
    const CLIENT_MAX_BODY_SIZE = 48; // in MBytes.
    const MAX_RETRIES = 6;

    const LOSSY_EXIF_TAG = "SPXLY";
    const LOSSLESS_EXIF_TAG = "SPXLL";

    const RESIZE_OUTER = 1;
    const RESIZE_INNER = 3;

    private static $key = NULL;
    private static $client = NULL;
    private static $options = array(
        "lossy" => 1, // 1 - lossy, 2 - glossy, 0 - lossless
        "keep_exif" => 0, // 1 - EXIF is preserved, 0 - EXIF is removed
        "resize" => 0, // 0 - don't resize, 1 - outer resize, 3 - inner resize
        "resize_width" => null, // in pixels. null means no resize
        "resize_height" => null, // in pixels. null means no resize
        "cmyk2rgb" => 1, // convert CMYK to RGB: 1 yes, 0 no
        "convertto" => "", // if '+webp' then also the WebP version will be generated, if +avif then also the AVIF version will be generated. Specify both with +webp|+avif
        "user" => "", //set the user needed for HTTP AUTH of the base_url
        "pass" => "", //se the pass needed for HTTP AUTH of the base_url
        // **** return options ****
        "notify_me" => null, // should contain full URL of of notification script (notify.php) - to be implemented
        "wait" => 30, // seconds
        // **** local options ****
        "total_wait" => 30, //seconds
        "base_url" => null, // base url of the images - used to generate the path for toFile by extracting from original URL and using the remaining path as relative path to base_path
        "url_filter" => false, //the URL filter will be applied on the full inside base_url. Current available URL filters: encode (for base64_encode)
        "base_source_path" => "", // base path of the local files
        "base_path" => false, // base path to save the files
        "backup_path" => false, // backup path, relative to the optimization folder (base_source_path)
        // **** persist options ****
        "persist_type" => null, // null - don't persist, otherwise "text" (.shortpixel text file in each folder), "exif" (mark in the EXIF that the image has been optimized) or "mysql" (to be implemented)
        "persist_name" => ".shortpixel",
        "notify_progress" => false,
        "cache_time" => 0 // number of seconds to cache the folder results - the *Persister classes will cache the getTodo results and retrieve them from memcache if it's available.
        //"persist_user" => "user", // only for mysql
        //"persist_pass" => "pass" // only for mysql
        // "" => null,
    );
    private static $curlOptions = array();

    public static $PROCESSABLE_EXTENSIONS = array('jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'gif', 'png', 'pdf');

    private static $persistersRegistry = array();

    /**
     * @param $key - the ShortPixel API Key
     */
    public static function setKey($key) {
        self::$key = $key;
        self::$client = NULL;
    }

    /**
     * @param $options - set the ShortPxiel options. Options defaults are the following:
     *  "lossy" => 1, // 1 - lossy, 0 - lossless
        "keep_exif" => 0, // 1 - EXIF is preserved, 0 - EXIF is removed
        "resize_width" => null, // in pixels. null means no resize
        "resize_height" => null,
        "cmyk2rgb" => 1,
        "convertto" => "", // if '+webp' then also the WebP version will be generated, if +avif then also the AVIF version will be generated. Specify both with +webp|+avif
        "notify_me" => null, // should contain full URL of of notification script (notify.php)- TO BE IMPLEMENTED
        "wait" => 30,
        //local options
        "total_wait" => 30,
        "base_url" => null, // base url of the images - used to generate the path for toFile by extracting from original URL and using the remaining path as relative path to base_path
        "base_path" => "/tmp", // base path for the saved files
     */
    public static function setOptions($options) {
        self::$options = array_merge(self::$options, $options);
    }

    /**
     * add custom cURL options. These provided options will take precedence to the default options that are passed to all cURL calls but not to others that are specific to each call (CURLOPT_TIMEOUT, CURLOPT_CUSTOMREQUEST)
     * or to library's user agent.
     * @param array $curlOptions Key-value pairs
     */
    public static function setCurlOptions($curlOptions) {
        self::$curlOptions = $curlOptions + self::$curlOptions;
    }

    /**
     * @return the API Key in use
     */
    public static function getKey() {
        return self::$key;
    }

    /**
     * @param $name - option name
     * @return the option value or false if not found
     */
    public static function opt($name) {
        return isset(self::$options[$name]) ? self::$options[$name] : false;
    }

    /**
     * @return the current options array
     */
    public static function options() {
        return self::$options;
    }

    /**
     * @return Client singleton
     * @throws AccountException
     */
    public static function getClient() {
        if (!self::$key) {
            throw new AccountException("Provide an API key with \MediaCloud\Vendor\ShortPixel\setKey(...)", -6);
        }

        if (!self::$client) {
            self::$client = new Client(self::$curlOptions);
        }

        return self::$client;
    }

    public static function getPersister($context = null) {
        if(!self::$options["persist_type"]) {
            return null;
        }
        if($context && isset(self::$persistersRegistry[self::$options["persist_type"] . $context])) {
            return self::$persistersRegistry[self::$options["persist_type"] . $context];
        }

        $persister = null;
        switch(self::$options["persist_type"]) {
            case "exif":
                $persister = new persist\ExifPersister(self::$options);
                break;
            case "mysql":
                return null;
            case "text":
                $persister = new persist\TextPersister(self::$options);
                break;
            default:
                throw new PersistException("Unknown persist type: " . self::$options["persist_type"]);
        }

        if($context) {
            self::$persistersRegistry[self::$options["persist_type"] . $context] = $persister;
        }
        return $persister;
    }

    static public function isProcessable($path) {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), \MediaCloud\Vendor\ShortPixel\ShortPixel::$PROCESSABLE_EXTENSIONS);
    }

    static public function log($msg) {
        if(ShortPixel::DEBUG_LOG) {
            @file_put_contents(__DIR__ . '/splog.txt', date("Y-m-d H:i:s") . " - " . $msg . " \n\n", FILE_APPEND);
        }
    }
}

ShortPixel::setOptions(array('base_path' => sys_get_temp_dir()));


/**
 * stub for ShortPixel::setKey()
 * @param $key - the ShortPixel API Key
 */
function setKey($key) {
    return ShortPixel::setKey($key);
}

/**
 * stub for ShortPixel::setOptions()
 * @return the current options array
 */
function setOptions($options) {
    return ShortPixel::setOptions($options);
}

/**
 * stub for ShortPixel::setOptions()
 * @return the current options array
 */
function setCurlOptions($options) {
    return ShortPixel::setCurlOptions($options);
}

/**
 * stub for ShortPixel::opt()
 * @param $name - name of the option
 * @return the option
 */
function opt($name) {
    return ShortPixel::opt($name);
}

/**
 * Stub for Source::fromFiles
 * @param $path - the file path on the local drive
 * @return Commander - the class that handles the optimization commands
 * @throws ClientException
 */
function fromFiles($path) {
    $source = new Source();
    return $source->fromFiles($path);
}

function fromFile($path) {
    return fromFiles($path);
}

/**
 * Stub for Source::folderInfo
 * @param $path - the file path on the local drive
 * @param bool $recurse - boolean - go into subfolders or not
 * @param bool $fileList - return the list of files with optimization status (only current folder, not subfolders)
 * @param array $exclude - array of folder names that you want to exclude from the optimization
 * @param bool $persistPath - the path where to look for the metadata, if different from the $path
 * @param int $recurseDepth - how many subfolders deep to go. Defaults to PHP_INT_MAX
 * @param bool $retrySkipped - if true, all skipped files will be reset to pending with retries = 0
 * @return object|void (object)array('status', 'total', 'succeeded', 'pending', 'same', 'failed')
 * @throws PersistException
 */
function folderInfo($path, $recurse = true, $fileList = false, $exclude = array(), $persistPath = false, $recurseDepth = PHP_INT_MAX, $retrySkipped = false) {
    $source = new Source();
    return $source->folderInfo($path, $recurse, $fileList, $exclude, $persistPath, $recurseDepth, $retrySkipped);
}

/**
 * Stub for Source::fromFolder
 * @param $path - the file path on the local drive
 * @param $maxFiles - maximum number of files to select from the folder
 * @param $exclude - exclude files based on regex patterns
 * @param $persistPath - the path where to store the metadata, if different from the $path (usually the target path)
 * @param $maxTotalFileSize - max summed up file size in MB
 * @return Commander - the class that handles the optimization commands
 * @throws ClientException
 */
function fromFolder($path, $maxFiles = ShortPixel::MAX_ALLOWED_FILES_PER_CALL, $exclude = array(), $persistPath = false, $maxTotalFileSize = ShortPixel::CLIENT_MAX_BODY_SIZE, $recurseDepth = PHP_INT_MAX) {
    $source = new Source();
    return $source->fromFolder($path, $maxFiles, $exclude, $persistPath, $maxTotalFileSize, $recurseDepth);
}

/**
 * Stub for Source::fromWebFolder
 * @param $path - the file path on the local drive
 * @param $webPath - the corresponding web path for the file path
 * @param array $exclude - exclude files based on regex patterns
 * @param bool $persistFolder - the path where to store the metadata, if different from the $path (usually the target path)
 * @param int $recurseDepth - how many subfolders deep to go. Defaults to PHP_INT_MAX
 * @return Commander - the class that handles the optimization commands
 * @throws ClientException
 */
function fromWebFolder($path, $webPath, $exclude = array(), $persistFolder = false, $recurseDepth = PHP_INT_MAX) {
    $source = new Source();
    return $source->fromWebFolder($path, $webPath, $exclude, $persistFolder, $recurseDepth);
}

function fromBuffer($name, $contents) {
    $source = new Source();
    return $source->fromBuffer($name, $contents);
}

/**
 * Stub for Source::fromUrls
 * @param $urls - the array of urls to be optimized
 * @return Commander - the class that handles the optimization commands
 * @throws ClientException
 */
function fromUrls($urls) {
    $source = new Source();
    return $source->fromUrls($urls);
}

/**
 */
function isOptimized($path) {
    $persist = ShortPixel::getPersister($path);
    if($persist) {
        return $persist->isOptimized($path);
    } else {
        throw new Exception("No persister available");
    }
}

function validate() {
    try {
        ShortPixel::getClient()->request("post");
    } catch (ClientException $e) {
        return true;
    }
}

function recurseCopy($source, $dest) {
    foreach (
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        if ($item->isDir()) {
            if(!@mkdir($target)) {
                throw new PersistException("Could not create directory $target. Please check rights.");
            }
        } else {
            if(!@copy($item, $target)) {
                throw new PersistException("Could not copy file $item to $target. Please check rights.");
            }
        }
    }
}

function delTree($dir, $keepBase = true) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file", false) : unlink("$dir/$file");
    }
    return $keepBase ? true : rmdir($dir);
}

/**
 * a basename alternative that deals OK with multibyte charsets (e.g. Arabic)
 * @param string $Path
 * @return string
 */
function MB_basename($Path, $suffix = false){
    $Separator = " qq ";
    $qqPath = preg_replace("/[^ ]/u", $Separator."\$0".$Separator, $Path);
    if(!$qqPath) { //this is not an UTF8 string!!
        $pathElements = explode('/', $Path);
        $fileName = end($pathElements);
        $pos = gettype($suffix) == 'string' ? strpos($fileName, $suffix) : false;
        if($pos !== false) {
            return substr($fileName, 0, $pos);
        }
        return $fileName;
    }
    $suffix = preg_replace("/[^ ]/u", $Separator."\$0".$Separator, $suffix);
    $Base = basename($qqPath, $suffix);
    $Base = str_replace($Separator, "", $Base);
    return $Base;
}

if(!function_exists('mb_detect_encoding')) {
    function mb_detect_encoding($string, $enc=null) {

        static $list = array('utf-8', 'iso-8859-1', 'windows-1251');

        foreach ($list as $item) {
            $sample = @iconv($item, $item, $string);
            if (md5($sample) == md5($string)) {
                if ($enc == $item) { return true; } else { return $item; }
            }
        }
        return null;
    }
}

function spdbg($var, $msg) {
    echo("DEBUG $msg : "); var_dump($var);
}

function spdbgd($var, $msg) {
    die(spdbg($var, $msg));
}

function normalizePath($path) {
    $abs = ($path[0] === '/') ? '/' : '';
    $path = str_replace(array('/', '\\'), '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return $abs . implode('/', $absolutes);
}

function getMemcache() {
    $mc = false;
    if(class_exists('\Memcached')) {
        $mc = new \Memcached();
        $mc->addServer('127.0.0.1', '11211');
        if(!@$mc->getStats()) {
            $mc = false;
        }
    }
    elseif(class_exists('\Memcache')) {
        $mc = new \Memcache();
        $mc->addServer('127.0.0.1', '11211');
        if(!@$mc->connect('127.0.0.1', '11211')) {
            $mc = false;
        } else {
            $mc->close();
        }
    }
    return $mc;
}

if ( ! function_exists( 'json_last_error_msg' ) ) {
    /**
     * Retrieves the error string of the last json_encode() or json_decode() call.
     *
     * @since 4.4.0
     *
     * @internal This is a compatibility function for PHP <5.5
     *
     * @return bool|string Returns the error message on success, "No Error" if no error has occurred,
     *                     or false on failure.
     */
    function json_last_error_msg()
    {
        // See https://core.trac.wordpress.org/ticket/27799.
        if (!function_exists('json_last_error')) {
            return false;
        }

        $last_error_code = json_last_error();

        // Just in case JSON_ERROR_NONE is not defined.
        $error_code_none = defined('JSON_ERROR_NONE') ? JSON_ERROR_NONE : 0;

        switch (true) {
            case $last_error_code === $error_code_none:
                return 'No error';

            case defined('JSON_ERROR_DEPTH') && JSON_ERROR_DEPTH === $last_error_code:
                return 'Maximum stack depth exceeded';

            case defined('JSON_ERROR_STATE_MISMATCH') && JSON_ERROR_STATE_MISMATCH === $last_error_code:
                return 'State mismatch (invalid or malformed JSON)';

            case defined('JSON_ERROR_CTRL_CHAR') && JSON_ERROR_CTRL_CHAR === $last_error_code:
                return 'Control character error, possibly incorrectly encoded';

            case defined('JSON_ERROR_SYNTAX') && JSON_ERROR_SYNTAX === $last_error_code:
                return 'Syntax error';

            case defined('JSON_ERROR_UTF8') && JSON_ERROR_UTF8 === $last_error_code:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';

            case defined('JSON_ERROR_RECURSION') && JSON_ERROR_RECURSION === $last_error_code:
                return 'Recursion detected';

            case defined('JSON_ERROR_INF_OR_NAN') && JSON_ERROR_INF_OR_NAN === $last_error_code:
                return 'Inf and NaN cannot be JSON encoded';

            case defined('JSON_ERROR_UNSUPPORTED_TYPE') && JSON_ERROR_UNSUPPORTED_TYPE === $last_error_code:
                return 'Type is not supported';

            default:
                return 'An unknown error occurred';
        }
    }
}
