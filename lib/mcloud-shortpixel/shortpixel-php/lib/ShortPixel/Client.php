<?php

namespace MediaCloud\Vendor\ShortPixel;


class Client {

    public static function API_DOMAIN() {
        return "api.shortpixel.com";
        //* DEVELOPMENT !! */ return "devapi2.shortpixel.com";
    }

    private $options, $customOptions, $logger;

    public static function API_URL() {
        return "https://" . self::API_DOMAIN();

    }
    public static function API_ENDPOINT() {
        return self::API_URL() . "/v2/reducer.php";
    }

    public static function API_UPLOAD_ENDPOINT() {
        return self::API_URL() . "/v2/post-reducer.php";
    }

    public static function API_STATUS_ENDPOINT() {
        return self::API_URL() . "/v2/api-status.php";
    }

    public static function IMAGE_STATUS_ENDPOINT() {
        return self::API_URL() . "/v2/image-status.php";
    }

    public static function CLEANUP_ENDPOINT() {
        return self::API_URL() . "/v2/cleanup.php";
    }

    public static function userAgent() {
        $curl = curl_version();
        return "ShortPixel/" . ShortPixel::VERSION . " PHP/" . PHP_VERSION . " curl/" . $curl["version"];
    }

    private static function caBundle() {
        return dirname(__DIR__) . "/data/shortpixel.crt";
    }

    function __construct($curlOptions) {
        $this->customOptions = $curlOptions;
        $this->logger = SPLog::Get(SPLog::PRODUCER_CLIENT);
        $this->options = $curlOptions + array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 60,
            //CURLOPT_CAINFO => self::caBundle(),
            CURLOPT_SSL_VERIFYPEER => false, //TODO true
            CURLOPT_SSL_VERIFYHOST => false, //TODO remove
            CURLOPT_USERAGENT => self::userAgent(),
        );
    }

    /**
     * Does the CURL request to the ShortPixel API
     * @param $method 'post' or 'get'
     * @param null $body - the POST fields
     * @param array $header - HTTP headers
     * @return array - metadata from the API
     * @throws ConnectionException
     */
    function request($method, $body = NULL, $header = array()){
        if ($body) {
            foreach($body as $key => $val) {
                if($val === null) {
                    unset($body[$key]);
                }
            }
        }

        ShortPixel::log("REQUEST BODY: " . json_encode($body));

        $retUrls = array("body" => array(), "headers" => array(), "fileMappings" => array());
        $retPend = array("body" => array(), "headers" => array(), "fileMappings" => array());
        $retFiles = array("body" => array(), "headers" => array(), "fileMappings" => array());

        if(isset($body["urllist"])) {
            $retUrls = $this->requestInternal($method, $body, $header);
        }
        if(isset($body["pendingURLs"])) {
            unset($body["urllist"]);
            //some files might have already been processed as relaunches in the given max time
            foreach($retUrls["body"] as $url) {
                //first remove it from the files list as the file was uploaded properly
                if($url->Status->Code != -102 && $url->Status->Code != -106) {
                    $notExpired[] = $url;
                    if(!isset($body["pendingURLs"][$url->OriginalURL])) {
                        $lala = "cucu";
                    } else
                    $unsetPath = $body["pendingURLs"][$url->OriginalURL];
                    if(isset($body["files"]) && ($key = array_search($unsetPath, $body["files"])) !== false) {
                        unset($body["files"][$key]);
                    }
                }
                //now from the pendingURLs if we already have an answer with urllist
                if(isset($body["pendingURLs"][$url->OriginalURL])) {
                    $retUrls["fileMappings"][$url->OriginalURL] = $body["pendingURLs"][$url->OriginalURL];
                    unset($body["pendingURLs"][$url->OriginalURL]);
                }
            }
            if(count($body["pendingURLs"])) {
                $retPend = $this->requestInternal($method, $body, $header);
                if(isset($retPend['body']->Status->Code) && $retPend['body']->Status->Code < 0) { //something's wrong (API key?)
                    throw new ClientException($retPend['body']->Status->Message, $retPend['body']->Status->Code);

                }
                if(isset($body["files"])) {
                    $notExpired = array();
                    foreach($retPend['body'] as $detail) {
                        if($detail->Status->Code != -102) { // -102 is expired, means we need to resend the image through post
                            $notExpired[] = $detail;
                            $unsetPath = $body["pendingURLs"][$detail->OriginalURL];
                            if(($key = array_search($unsetPath, $body["files"])) !== false) {
                                unset($body["files"][$key]);
                            }
                        }
                    }
                    $retPend['body'] = $notExpired;
                }
            }
        }
        if (isset($body["files"]) && count($body["files"]) ||
            isset($body["buffers"]) && count($body["buffers"])) {
            unset($body["pendingURLs"]);
            $retFiles = $this->requestInternal($method, $body, $header);
        }

        if(!isset($retUrls["body"]->Status) && !isset($retPend["body"]->Status) && !isset($retFiles["body"]->Status)
           && (!is_array($retUrls["body"]) || !is_array($retPend["body"]) || !is_array($retFiles["body"]))) {
            throw new Exception("Request inconsistent status. Please contact support.");
        }

        $body = isset($retUrls["body"]->Status)
            ? $retUrls["body"]
            : (isset($retPend["body"]->Status)
                ? $retPend["body"]
                : (isset($retFiles["body"]->Status)
                    ? $retFiles["body"] :
                    array_merge($retUrls["body"], $retPend["body"], $retFiles["body"])));

        $theReturn =  (object) array("body"    => $body,
                     "headers" => array_unique(array_merge($retUrls["headers"], $retPend["headers"], $retFiles["headers"])),
                     "fileMappings" => array_merge($retUrls["fileMappings"], $retPend["fileMappings"], $retFiles["fileMappings"]));
        ShortPixel::log("REQUEST RETURNS: " . json_encode($theReturn));
        return $theReturn;
    }

    function requestInternal($method, $body = NULL, $header = array()){
        $request = curl_init();
        curl_setopt_array($request, $this->options);

        $files = $urls = false;

        if (isset($body["urllist"])) { //images are sent as a list of URLs
            $this->prepareJSONRequest(self::API_ENDPOINT(), $request, $body, $method, $header);
        }
        elseif(isset($body["pendingURLs"])) {
            //prepare the pending items request
            $urls = array();
            $fileCount = 1;
            foreach($body["pendingURLs"] as $url => $path) {
                $urls["url" . $fileCount] = $url;
                $fileCount++;
            }
            $pendingURLs = $body["pendingURLs"];
            unset($body["pendingURLs"]);
            $body["file_urls"] = $urls;
            $this->prepareJSONRequest(self::API_UPLOAD_ENDPOINT(), $request, $body, $method, $header);
        }
        elseif (isset($body["files"]) || isset($body["buffers"])) {
            $files = $this->prepareMultiPartRequest($request, $body, $header);
        }
        else {
            return array("body" => array(), "headers" => array(), "fileMappings" => array());
        }

        //spdbgd(rawurldecode($body['urllist'][1]), "body");

        list($details, $headers, $status, $response) = $this->sendRequest($request,6);

        //TODO delete later
/*        for($i = 0; $i < 6; $i++) { //curl_setopt($request, CURLOPT_TIMEOUT, 120);curl_setopt($request, CURLOPT_VERBOSE, true);
            $response = curl_exec($request);
            if(!curl_errno($request)) {
                break;
            } else {
                ShortPixel::log("CURL ERROR: " . curl_error($request) . " (BODY: $response)");
            }
        }

        if(curl_errno($request)) {
            throw new ConnectionException("Error while connecting: " . curl_error($request) . "");
        }
        if (!is_string($response)) {
            $message = sprintf("%s (#%d)", curl_error($request), curl_errno($request));
            curl_close($request);
            throw new ConnectionException("Error while connecting: " . $message);
        }

        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($request, CURLINFO_HEADER_SIZE);
        curl_close($request);

        $headers = self::parseHeaders(substr($response, 0, $headerSize));
        $body = substr($response, $headerSize);

        $details = json_decode($body);

        if (!$details) {
            $message = sprintf("Error while parsing response (Status: %s): %s (#%d)", $status,
                PHP_VERSION_ID >= 50500 ? json_last_error_msg() : "Error",
                json_last_error());
            $details = (object) array(
                "raw" => $body,
                "error" => "ParseError",
                "message" => $message . "( " . $body . ")",
                "Status" => (object)array("Code" => -1, "Message" => "ParseError: " . $message)
            );
        }
*/
        if(getenv("SHORTPIXEL_DEBUG")) {
            $info = "DETAILS\n";
            if(is_array($details)) {
                foreach($details as $det) {
                    $info .= $det->Status->Code . " " . $det->OriginalURL . (isset($det->localPath) ? "({$det->localPath})" : "" ) . "\n";
                }
            } else {
                $info = $response;
            }
        }

        $fileMappings = array();
        if($files) {
            $fileMappings = array();
            foreach($details as $detail) {
                if(isset($detail->Key) && isset($files[$detail->Key])){
                    $fileMappings[$detail->OriginalURL] = $files[$detail->Key];
                }
            }
        } elseif($urls) {
            $fileMappings = $pendingURLs;
        }

        if(getenv("SHORTPIXEL_DEBUG")) {
            $info .= "FILE MAPPINGS\n";
            foreach($fileMappings as $key => $val) {
                $info .= "$key -> $val\n";
            }
        }

        if ($status >= 200 && $status <= 299) {
            return array("body" => $details, "headers" => $headers, "fileMappings" => $fileMappings);
        }

        throw Exception::create($details->message, $details->error, $status);
    }

    protected function sendRequest($request, $tries) {
        for($i = 0; $i < $tries; $i++) { //curl_setopt($request, CURLOPT_TIMEOUT, 120);curl_setopt($request, CURLOPT_VERBOSE, true);
            $response = curl_exec($request);
            $this->logger->log(SPLog::PRODUCER_CLIENT, "RAW RESPONSE: " . $response);
            if(!curl_errno($request)) {
                break;
            } else {
                ShortPixel::log("CURL ERROR: " . curl_error($request) . " (BODY: $response)");
            }
        }

        if(curl_errno($request)) {
            throw new ConnectionException("Error while connecting: " . curl_error($request) . "");
        }
        if (!is_string($response)) {
            $message = sprintf("%s (#%d)", curl_error($request), curl_errno($request));
            curl_close($request);
            throw new ConnectionException("Error while connecting: " . $message);
        }

        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($request, CURLINFO_HEADER_SIZE);
        curl_close($request);

        $headers = self::parseHeaders(substr($response, 0, $headerSize));
        $body = substr($response, $headerSize);

        $details = json_decode($body);

        if (!$details) {
            $message = sprintf("Error while parsing response (Status: %s): %s (#%d)", $status,
                PHP_VERSION_ID >= 50500 ? json_last_error_msg() : "Error",
                json_last_error());
            $details = (object) array(
                "raw" => $body,
                "error" => "ParseError",
                "message" => $message . "( " . $body . ")",
                "Status" => (object)array("Code" => -1, "Message" => "ParseError: " . $message)
            );
            ShortPixel::log("JSON Error while parsing response: " . json_encode($details));
        }
        return array($details, $headers, $status, $response);
    }

    protected function prepareJSONRequest($endpoint, $request, $body, $method, $header) {
        //to escape the + from "+webp"
        if(isset($body["convertto"]) && $body["convertto"]) {
            $converts = explode('|', $body["convertto"]);
            $body["convertto"] = implode('|', array_map('urlencode', $converts ));
        }
//        if(isset($body["urllist"])) {
//            aici folosim ceva de genul: parse_url si apoi pe partea de path: str_replace('%2F', '/', rawurlencode($this->filePath)
//            $body["urllist"] = array_map('rawurlencode', $body["urllist"]);
//        }
        if(isset($body["buffers"])) unset($body['buffers']);
        
        $body = json_encode($body);

        array_push($header, "Content-Type: application/json");
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($request, CURLOPT_HTTPHEADER, $header);
        if ($body) {
            curl_setopt($request, CURLOPT_POSTFIELDS, $body);
        }
    }


    protected function prepareMultiPartRequest($request, $body, $header) {
        $files = array();
        $fileCount = 1;
        //to escape the + from "+webp"
        if($body["convertto"]) {
            $body["convertto"] = urlencode($body["convertto"]);
        }
        if(isset($body["files"])) {
            foreach($body["files"] as $filePath) {
                $files["file" . $fileCount] = $filePath;
                $fileCount++;
            }
        }
        $buffers = array();
        if(isset($body["buffers"])) {
            foreach($body["buffers"] as $name => $contents) {
                $files["file" . $fileCount] = $name;
                $buffers["file" . $fileCount] = $contents;
                $fileCount++;
            }
            unset($body["buffers"]);
        }
        $body["file_paths"] = json_encode($files);
        unset($body["files"]);
        curl_setopt($request, CURLOPT_URL, Client::API_UPLOAD_ENDPOINT());
        $this->curl_custom_postfields($request, $body, $files, $header, $buffers);
        return $files;
    }

    function curl_custom_postfields($ch, array $assoc = array(), array $files = array(), $header = array(), $buffers = array()) {

        // invalid characters for "name" and "filename"
        static $disallow = array("\0", "\"", "\r", "\n");

        // build normal parameters
        foreach ($assoc as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v),
            ));
        }

        // build file parameters
        $fileContents = array();
        foreach ($files as $k => $v) {
            switch (true) {
                case true === $v = realpath(filter_var($v)):
                case is_file($v):
                case is_readable($v):
                    $fileContents[$k] = file_get_contents($v);
                    // continue; // or return false, throw new InvalidArgumentException
            }
        }
        $fileContents = array_merge($fileContents, $buffers);

        foreach ($fileContents as $k => $data) {
            $pp = explode(DIRECTORY_SEPARATOR, $files[$k]);
            $v = end($pp);
            $k = str_replace($disallow, "_", $k);
            $v = str_replace($disallow, "_", $v);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
            ));
        }

        // generate safe boundary
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

        // add boundary for each parameters
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });

        // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";

        // set options
        return @curl_setopt_array($ch, array(
            CURLOPT_POST       => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300, //to be able to handle via post large files up to 48M which might take a long time to upload.
            CURLOPT_POSTFIELDS => implode("\r\n", $body),
            CURLOPT_HTTPHEADER => array_merge(array(
                "Expect: 100-continue",
                "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
            ), $header),
        ));
    }

    protected static function parseHeaders($headers) {
        if (!is_array($headers)) {
            $headers = explode("\r\n", $headers);
        }

        $res = array();
        foreach ($headers as $header) {
            if (empty($header)) continue;
            $split = explode(":", $header, 2);
            if (count($split) === 2) {
                $res[strtolower($split[0])] = trim($split[1]);
            }
        }
        return $res;
    }

    function download($sourceURL, $target, $expectedSize = false) {
        $targetTemp = substr($target, 0, 245) . ".sptemp";
        $fp = @fopen ($targetTemp, 'w+');              // open file handle
        if(!$fp) {
            //file cannot be opened, probably no rights or path disappeared
            if(!is_dir(dirname($target))) {
                throw new ClientException("The file path cannot be found.", -15);
            } else {
                throw new ClientException("Temp file cannot be created inside " . dirname($targetTemp) . ". Please check rights.", -16);
            }
        }

        $ch = curl_init($sourceURL);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
        curl_setopt_array($ch, $this->customOptions);
        curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); //previously 1. Changed it because it conflicts with some clients open_basedir (php.ini) settings (https://secure.helpscout.net/conversation/859529984/16086?folderId=1117588)
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);      // some large value to allow curl to run for a long time
        curl_setopt($ch, CURLOPT_USERAGENT, $this->options[CURLOPT_USERAGENT]);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);   // Enable this line to see debug prints
        curl_exec($ch);

        curl_close($ch);                              // closing curl handle
        fclose($fp);                                  // closing file handle
        $actualSize = filesize($targetTemp);
        if(!$expectedSize || $expectedSize == $actualSize) {
            if(!@rename($targetTemp, $target)) {
                @unlink($targetTemp);
                throw new ClientException("File cannot be renamed. Please check rights.", -16);
            }
        } else {
            $meta = ($actualSize < 200 ? json_decode(file_get_contents($targetTemp)) : false);
            if(isset($meta->Status->Code) && $meta->Status->Code === '-302') {
                $this->logger->log(SPLog::PRODUCER_CLIENT, "File is gone on the server, needs to be resent.", $meta);
            }
            // ATENTIE!!!!! daca s-a oprit aici e un caz de fisier cu dimensiunea diferita, de verificat
            @unlink($targetTemp);
            return -$actualSize; //will retry
        }
        return true;
    }

    function apiStatus($key, $domainToCheck = false, $imgCount = 0, $thumbsCount = 0) {
        $request = curl_init();
        curl_setopt_array($request, $this->options);
        //$this->prepareJSONRequest(self::API_STATUS_ENDPOINT(), $request, array('key' => $key), 'post', array());
        curl_setopt($request, CURLOPT_URL, self::API_STATUS_ENDPOINT());
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($request, CURLOPT_HTTPHEADER, array());
        $params = array('key' => $key);
        if($domainToCheck) {
            $params['DomainCheck'] = $domainToCheck;
            $params['ImagesCount'] = $imgCount;
            $params['ThumbsCount'] = $thumbsCount;
            
        }
        curl_setopt($request, CURLOPT_POSTFIELDS, $params);
        return $this->sendRequest($request, 1);
    }

    /**
     * Method that checks the status of an image being optimized
     * @param $key
     * @param $url
     * @return array
     * @throws ConnectionException
     */
    function imageStatus($key, $url) {
        $request = curl_init();
        curl_setopt_array($request, $this->options);
        //$this->prepareJSONRequest(self::API_STATUS_ENDPOINT(), $request, array('key' => $key), 'post', array());
        curl_setopt($request, CURLOPT_URL, self::IMAGE_STATUS_ENDPOINT());
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($request, CURLOPT_HTTPHEADER, array());
        $params = array('key' => $key, 'url' => $url);
        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($params));
        return $this->sendRequest($request, 1);
    }

    /**
     * method that dumps the image from the optimization queue so the optimized version isn't available any more.
     * Useful when you MIGHT need to optimize another image with the same URL - but with different contents - in the next
     * hour and you don't want to have to keep a status to tell you if you need to use refresh() or not...
     * @param $key
     * @param $urllist
     * @return array
     * @throws ConnectionException
     */
    function imageCleanup($key, $urllist) {
        $request = curl_init();
        curl_setopt_array($request, $this->options);
        //$this->prepareJSONRequest(self::API_STATUS_ENDPOINT(), $request, array('key' => $key), 'post', array());
        curl_setopt($request, CURLOPT_URL, self::CLEANUP_ENDPOINT());
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($request, CURLOPT_HTTPHEADER, array());
        $params = array('key' => $key, 'urllist' => $urllist);
        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($params));
        return $this->sendRequest($request, 1);
    }
}
