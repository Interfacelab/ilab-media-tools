<?php
use \Sarciszewski\PHPFuture as Future;
spl_autoload_register(function ($class) {
    
    // project-specific namespace prefix
    $prefix = 'MediaCloud\\Vendor\\Sarciszewski\PHPFuture\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';
    
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    
    // get the relative class name
    $relative_class = substr($class, $len);
    
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


if (!interface_exists('JsonSerializable')) {
    /**
     * From PHP 5.4
     *
     * @ref http://us3.php.net/manual/en/class.jsonserializable.php
     */
    interface JsonSerializable
    {
        public function jsonSerialize();
    }
}

if (!interface_exists('SessionHandlerInterface')) {

    /**
     * From PHP 5.4
     *
     * @ref http://php.net/manual/en/class.sessionhandlerinterface.php
     */
    interface SessionHandlerInterface
    {
        public function close();
        public function destroy($session_id);
        public function gc($maxlifetime);
        public function open($save_path, $name);
        public function read($session_id);
        public function write($session_id, $session_data);
    }
}


if (!function_exists('array_column')) {
    /**
     * Get the boolean value of a variable
     */
    function array_column(array $array, $column_key, $index_key = null)
    {
        return Future\Utility::arrayColumn($array, $column_key, $index_key);
    }
}

if (!function_exists('boolval')) {
    /**
     * Get the boolean value of a variable
     */
    function boolval($mixed_var)
    {
        return !!$mixed_var;
    }
}

if (!function_exists('hex2bin')) {
    /**
     * From PHP 5.4
     *
     * @ref https://php.net/hex2bin
     *
     * @param string $data
     *
     * @return string
     */
    function hex2bin($data)
    {
        return Future\Utility::hexToBin($data);
    }
}

if (!function_exists('hash_equals')) {
    /**
     * From PHP 5.6
     * 
     * @ref https://php.net/hash_equals
     *
     * @param string $known_string
     * @param string $user_string
     *
     * @return boolean
     */
    function hash_equals($known_string, $user_string)
    {
        return Future\Security::hashEquals(
            $known_string,
            $user_string
        );
    }
}

if (!function_exists('hash_pbkdf2')) {
    /**
     * From PHP 5.5
     *
     * @ref https://php.net/hash_pbkdf2
     *
     * @param string $algo
     * @param string $password
     * @param string $salt
     * @param int $iterations
     * @param int $length
     * @param boolean $raw_output
     *
     * @return string
     */
    function hash_pbkdf2($algo, $password, $salt, $iterations, $length = 0, $raw_output = false)
    {
        $key = Future\Security::pbkdf2(
            $algo,
            $password,
            $salt,
            $iterations,
            $length
        );
        if ($raw_output) {
            return bin2hex($key);
        }
        return $key;
    }
}

if (!function_exists('getimagesizefromstring')) {
    /**
     * From PHP 5.4
     *
     * @ref https://php.net/getimagesizefromstring
     *
     * @param string $imagedata
     * @param &array $imageinfo
     *
     * @return array
     */
    function getimagesizefromstring($imagedata, &$imageinfo = null)
    {
        if ($imageinfo !== null) {
            return Future\Image::imageSizeFromString($imagedata, $imageinfo);
        }
        return Future\Image::imageSizeFromString($imagedata);
    }
}

if (!function_exists('openssl_pbkdf2')) {
    /**
     * From PHP 5.5
     *
     * @ref https://php.net/openssl_pbkdf2
     *
     * @param string $password
     * @param string $salt
     * @param int $length
     * @param int $iterations
     * @param string $algo
     *
     * @return string
     */
    function openssl_pbkdf2($password, $salt, $length, $iterations, $algo = 'sha1')
    {
        $key = Future\Security::pbkdf2(
            $algo,
            $password,
            $salt,
            $iterations,
            $length
        );
        return $key;
    }
}
