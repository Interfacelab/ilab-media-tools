<?php

namespace MediaCloud\Vendor\ShortPixel;

class Source {
    private $urls;

    /**
     * @param $paths
     * @param $basePath - common base path used to determine the subfolders that will be created in the destination
     * @param null $pending
     * @param bool $refresh
     * @return Commander - the class that handles the optimization commands
     * @throws ClientException
     * @internal param $path - the file path on the local drive
     */
    public function fromFiles($paths, $basePath = null, $pending = null, $refresh = false) {
        if(!is_array($paths)) {
            $paths = array($paths);
        }
        if(count($paths) > ShortPixel::MAX_ALLOWED_FILES_PER_CALL) {
            throw new ClientException("Maximum 10 local images allowed per call.");
        }
        $files = array();
        foreach($paths as $path) {
            if (!file_exists($path)) throw new ClientException("File not found: " . $path);
            if (is_dir($path)) throw new ClientException("For folders use fromFolder: " . $path);
            $files[] = $path;
        }
        $data       = array(
            "plugin_version" => ShortPixel::LIBRARY_CODE . " " . ShortPixel::VERSION,
            "key" =>  ShortPixel::getKey(),
            "files" => $files
        );
        if($refresh) { //don't put it in the array above because false will overwrite the commands refresh. If only set when true, will just force a refresh when needed.
            $data["refresh"] = 1;
        }
        if($pending && count($pending)) {
            $data["pendingURLs"] = $pending;
        }

        return new Commander($data, $this);
    }

    /**
     * returns the optimization counters of the folder and subfolders
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
    public function folderInfo($path, $recurse = true, $fileList = false, $exclude = array(), $persistPath = false, $recurseDepth = PHP_INT_MAX, $retrySkipped = false){
        $path = rtrim($path, '/\\');
        $persistPath = $persistPath ? rtrim($persistPath, '/\\') : false;
        $persister = ShortPixel::getPersister($path);
        if(!$persister) {
            throw new PersistException("Persist is not enabled in options, needed for fetching folder info");
        }
        return $persister->info($path, $recurse, $fileList, $exclude, $persistPath, $recurseDepth, $retrySkipped);
    }

    /**
     * processes a chunk of MAX_ALLOWED files from the folder, based on the persisted information about which images are processed and which not. This information is offered by the Persister object.
     * @param $path - the folder path on the local drive
     * @param int $maxFiles - maximum number of files to select from the folder
     * @param array $exclude - exclude files based on regex patterns
     * @param bool $persistFolder - the path where to store the metadata, if different from the $path (usually the target path)
     * @param int $maxTotalFileSize - max summed up file size in MB
     * @param int $recurseDepth - how many subfolders deep to go. Defaults to PHP_INT_MAX
     * @return Commander - the class that handles the optimization commands
     * @throws ClientException
     * @throws PersistException
     */
    public function fromFolder($path, $maxFiles = 0, $exclude = array(), $persistFolder = false, $maxTotalFileSize = ShortPixel::CLIENT_MAX_BODY_SIZE, $recurseDepth = PHP_INT_MAX) {
        if($maxFiles == 0) {
            $maxFiles = ShortPixel::MAX_ALLOWED_FILES_PER_CALL;
        }
        //sanitize
        $maxFiles = max(1, min(ShortPixel::MAX_ALLOWED_FILES_PER_CALL, intval($maxFiles)));
        $path = rtrim($path, '/\\');
        $persistFolder = $persistFolder ? rtrim($persistFolder, '/\\') : false;

        $persister = ShortPixel::getPersister($path);
        if(!$persister) {
            throw new PersistException("Persist_type is not enabled in options, needed for folder optimization");
        }
        $paths = $persister->getTodo($path, $maxFiles, $exclude, $persistFolder, $maxTotalFileSize, $recurseDepth);
        if($paths) {
            ShortPixel::setOptions(array("base_source_path" => $path));
            return $this->fromFiles($paths->files, null, $paths->filesPending);
        }
        throw new ClientException("Couldn't find any processable file at given path ($path).", 2);
    }

    /**
     * processes a chunk of MAX_ALLOWED URLs from a folder that is accessible via web at the $webPath location,
     * based on the persisted information about which images are processed and which not. This information is offered by the Persister object.
     * @param $path - the folder path on the local drive
     * @param $webPath - the web URL of the folder
     * @param array $exclude - exclude files based on regex patterns
     * @param bool $persistFolder - the path where to store the metadata, if different from the $path (usually the target path)
     * @param int $recurseDepth - how many subfolders deep to go. Defaults to PHP_INT_MAX
     * @return Commander - the class that handles the optimization commands
     * @throws ClientException
     * @throws PersistException
     */
    public function fromWebFolder($path, $webPath, $exclude = array(), $persistFolder = false, $recurseDepth = PHP_INT_MAX) {

        $path = rtrim($path, '/');
        $webPath = rtrim($webPath, '/');
        $persister = ShortPixel::getPersister();
        if($persister === null) {
            //cannot optimize from folder without persister.
            throw new PersistException("Persist_type is not enabled in options, needed for folder optimization");
        }
        $paths = $persister->getTodo($path, ShortPixel::MAX_ALLOWED_FILES_PER_WEB_CALL, $exclude, $persistFolder, $recurseDepth);
        $repl = (object)array("path" => $path . '/', "web" => $webPath . '/');
        if($paths && count($paths->files)) {
            $items = array_merge($paths->files, array_values($paths->filesPending)); //not impossible to have filesPending - for example optimized partially without webPath then added it
            array_walk(
                $items,
                function(&$item, $key, $repl){
                    $relPath = str_replace($repl->path, '', $item);
                    $item = implode('/', array_map('rawurlencode', explode('/', $relPath)));
                    $item = $repl->web . Source::filter($item);
                }, $repl);
            ShortPixel::setOptions(array("base_url" => $webPath, "base_source_path" => $path));

            return $this->fromUrls($items);
        }
        //folder is either empty, either fully optimized, in both cases it's optimized :)
        throw new ClientException("Couldn't find any processable file at given path ($path).", 2);
    }

    public function fromBuffer($name, $contents) {
        return new Commander(array(
            "plugin_version" => ShortPixel::LIBRARY_CODE . " " . ShortPixel::VERSION,
            "key" =>  ShortPixel::getKey(),
            "buffers" => array($name => $contents),
            // don't add it if false, otherwise will overwrite the refresh command //"refresh" => false
        ), $this);
    }

    /**
     * @param $urls - the array of urls to be optimized
     * @return Commander - the class that handles the optimization commands
     * @throws ClientException
     */
    public function fromUrls($urls) {
        if(!is_array($urls)) {
            $urls = array($urls);
        }
        if(count($urls) > ShortPixel::MAX_API_ALLOWED_FILES_PER_WEB_CALL) {
            throw new ClientException("Maximum 100 images allowed per call.");
        }

        $this->urls = array_map ('utf8_encode',  $urls);
        $data       = array(
            "plugin_version" => ShortPixel::LIBRARY_CODE . " " . ShortPixel::VERSION,
            "key" =>  ShortPixel::getKey(),
            "urllist" => $this->urls,
            // don't add it if false, otherwise will overwrite the refresh command //"refresh" => false
        );

        return new Commander($data, $this);
    }
    
    protected static function filter($item) {
        if(ShortPixel::opt('url_filter') == 'encode') {
            //TODO apply base64 or crypt on $item, whichone makes for a shorter string.
            $extPos = strripos($item,".");
            $extension = substr($item,$extPos + 1);
            $item = substr($item, 0, $extPos);
            //$ExtensionContentType = ( $extension == "jpg" ) ? "jpeg" : $extension;
            $item = base64_encode($item).'.'.$extension;
            SPLog::Get(SPLog::PRODUCER_SOURCE)->log(SPLog::PRODUCER_SOURCE, "ENCODED URL PART: " . $item);
        }
        return $item;
    }
}
