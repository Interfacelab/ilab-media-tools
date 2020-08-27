<?php
/**
 * User: simon
 * Date: 19.08.2016
 * Time: 18:05
 */

namespace MediaCloud\Vendor\ShortPixel\persist;
use MediaCloud\Vendor\ShortPixel\ClientException;
use \MediaCloud\Vendor\ShortPixel\Persister;
use \MediaCloud\Vendor\ShortPixel\ShortPixel;
use \MediaCloud\Vendor\ShortPixel\Client;
use \MediaCloud\Vendor\ShortPixel\SPCache;
use MediaCloud\Vendor\ShortPixel\SPLog;

/**
 * Class TextPersister - save the optimization information in .shortpixel files in the current folder of the images
 * @package MediaCloud\Vendor\ShortPixel\persist
 */
class TextPersister implements Persister {

    private $fp;
    private $options;
    private $logger;
    private $cache;
    private STATIC $ALLOWED_STATUSES = array('pending', 'success', 'skip', 'deleted');
    private STATIC $ALLOWED_TYPES = array('I', 'D');

    function __construct($options)
    {
        $this->options = $options;
        $this->fp = array();
        $this->logger = SPLog::Get(SPLog::PRODUCER_PERSISTER);
        $this->cache = SPCache::Get();
    }

    public static function IGNORED_BY_DEFAULT() {
        return array('.','..','.shortpixel','.sp-options','.sp-lock','.sp-progress','ShortPixelBackups');
    }

    function isOptimized($path)
    {
        if(!file_exists($path)) {
            return false;
        }
        $fp = $this->openMetaFile(dirname($path), 'read');
        if(!$fp) {
            return false;
        }

        while (($line = fgets($fp)) !== FALSE) {
            $data = $this->parse($line);
            if($data->file === \MediaCloud\Vendor\ShortPixel\MB_basename($path) && $data->status == 'success' ) {
                return true;
            }
        }
        fclose($fp);

        return false;
    }

    protected function ignored($exclude) {
        return array_values(array_merge(self::IGNORED_BY_DEFAULT(), is_array($exclude) ? $exclude : array()));
    }

    /**
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
    function    info($path, $recurse = true, $fileList = false, $exclude = array(), $persistPath = false, $recurseDepth = PHP_INT_MAX, $retrySkipped = false) {
        if($persistPath === false) {
            $persistPath = $path;
        }
        $toClose = false; $persistFolder = false;
        $info = array('status' => 'error', 'message' => "Unknown error, please contact support.", 'code' => -999);

        try {
            if(is_dir($path)) {
                try {
                    $persistFolder = $persistPath;
                    $toClose = $this->openMetaFileIfNeeded($persistFolder);
                    $fp = $this->getMetaFile($persistPath);
                    $dataArr = $this->readMetaFile($fp);
                } catch(ClientException $e) {
                    if(is_dir($persistPath) && file_exists($persistPath . '/' . ShortPixel::opt("persist_name"))) {
                        throw $e; //rethrow, there's a problem with the meta file.
                    }
                    $dataArr = array(); //there's no problem if the metadata file is missing and cannot be created, for the info call
                }

                $info = (object)array('status' => 'pending', 'total' => 0, 'succeeded' => 0, 'pending' => 0, 'same' => 0, 'failed' => 0, 'totalSize'=> 0, 'totalOptimizedSize'=> 0, 'todo' => null);
                $files = scandir($path);
                $ignore = $this->ignored($exclude);

                foreach($files as $file) {
                    $filePath = $path . '/' . $file;
                    $targetFilePath = $persistPath . '/' . $file;
                    if (in_array($file, $ignore)
                        || (!ShortPixel::isProcessable($file) && !is_dir($filePath))
                        || isset($dataArr[$file]) && $dataArr[$file]->status == 'deleted'
                    ) {
                        continue;
                    }
                    if (is_dir($filePath)) {
                        if(!$recurse || $recurseDepth <= 0) continue;
                        $subInfo = $this->info($filePath, $recurse, $fileList, $exclude, $targetFilePath, $recurseDepth - 1);
                        if($subInfo->status == 'error') {
                            $info = $subInfo;
                            break;
                        }
                        $info->total += $subInfo->total;
                        $info->succeeded += $subInfo->succeeded;
                        $info->pending += $subInfo->pending;
                        $info->same += $subInfo->same;
                        $info->failed += $subInfo->failed;
                        $info->totalSize += $subInfo->totalSize;
                        $info->totalOptimizedSize += $subInfo->totalOptimizedSize;
                    }
                    else {
                        $info->total++;
                        if(!isset($dataArr[$file]) || $dataArr[$file]->status == 'pending') {
                            $info->pending++;
                            $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->info - PENDING STATUS: $path/$file");
                        }
                        elseif(   $dataArr[$file]->status == 'success' && $this->isChanged($dataArr[$file], $file, $persistPath, $path)
                            // || ($dataArr[$file]->status == 'skip' &&  ($dataArr[$file]->retries <= ShortPixel::MAX_RETRIES || $retrySkipped))) {
                               || ($dataArr[$file]->status == 'skip' && $retrySkipped)) {
                            //file changed since last optimized, mark it as pending
                            $dataArr[$file]->status = 'pending';
                            if($dataArr[$file]->status == 'skip' && $retrySkipped) {
                                $dataArr[$file]->retries = 0;
                            } else {
                                $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->info - CHANGED - REVERT TO PENDING: $path/$file");
                            }
                            $this->updateMeta($dataArr[$file], $fp);
                            $info->pending++;
                        }
                        elseif($dataArr[$file]->status == 'success') {
                            if($dataArr[$file]->percent > 0) {
                                $info->succeeded++;
                                $info->totalOptimizedSize += $dataArr[$file]->optimizedSize;
                                $info->totalSize += round(100.0 * $dataArr[$file]->optimizedSize / (100.0 - $dataArr[$file]->percent));
                            } else {
                                $info->same++;
                            }
                        }
                        elseif($dataArr[$file]->status == 'skip'){
                            $info->failed++;
                        }
                    }
                    if($fileList) $info->fileList = $dataArr;
                }

                if(isset($info->pending) && $info->pending == 0 && $info->status !== 'error') {
                    $info->status = 'success';
                }
                if($info->status !== 'error') {
                    $info->todo = $this->getTodoInternal($files, $dataArr, $fp, $path, 1, $exclude, $persistPath, ShortPixel::CLIENT_MAX_BODY_SIZE, $recurseDepth);
                }
            }
            else {
                if(!file_exists($persistPath)) {
                    throw new ClientException("File not found: $persistPath", -15);
                }
                $persistFolder = dirname($persistPath);
                $meta = $toClose = false;
                try {
                    $toClose = $this->openMetaFileIfNeeded($persistFolder);
                    $meta = $this->findMeta($persistPath);
                } catch(ClientException $e) {
                    if(is_dir($persistFolder) && file_exists($persistFolder . '/' . ShortPixel::opt("persist_name"))) {
                        throw $e;
                    }
                }

                if(!$meta) {
                    $info = (object)array('status' => 'pending');
                } else {
                    $info = (object)array('status' => $meta->getStatus());
                }

            }
        }
        catch(ClientException $e) {
            $info = (object)array('status' => 'error', 'message' => $e->getMessage(), 'code' => $e->getCode());
        }
        catch(\Exception $e) { //that should've been a finally but we need to be PHP5.4 compatible...
            if($toClose) {
                $this->closeMetaFile($persistFolder);
            }
            throw $e;
        }
        if($toClose) {
            $this->closeMetaFile($persistFolder);
        }
        return $info;
    }

    function getTodo($path, $count, $exclude = array(), $persistPath = false, $maxTotalFileSizeMb = ShortPixel::CLIENT_MAX_BODY_SIZE, $recurseDepth = PHP_INT_MAX)
    {
        if(!file_exists($path) || !is_dir($path)) {
            $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - file not found or not a directory: $path");
            return array();
        }
        if(!$persistPath) {$persistPath = $path;}

        $toClose = $this->openMetaFileIfNeeded($persistPath);
        $fp = $this->getMetaFile($persistPath);

        $files = scandir($path);
        $dataArr = $this->readMetaFile($fp);

        $ret = $this->getTodoInternal($files, $dataArr, $fp, $path, $count, $exclude, $persistPath, $maxTotalFileSizeMb, $recurseDepth);

        if($toClose) { $this->closeMetaFile($persistPath); }

        if(count($ret->files) + count($ret->filesPending) + $ret->filesWaiting == 0) {
            $this->logger->logFirst($path, SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - FOR $path RETURN NONE");
        } else {
            $this->logger->clearLogged(SPLog::PRODUCER_PERSISTER, $path);
            $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - FOR $path RETURN", $ret);
        }
        return $ret;
    }


    protected function getTodoInternal(&$files, &$dataArr, $fp, $path, $count, $exclude, $persistPath, $maxTotalFileSizeMb, $recurseDepth)
    {
        $results = array();
        $pendingURLs = array();
        $ignore = $this->ignored($exclude);
        $remain = $count;
        $maxTotalFileSize = $maxTotalFileSizeMb * pow(1024, 2);
        $totalFileSize = 0;
        $filesWaiting = 0;
        foreach($files as $file) {
            $filePath = $path . '/' . $file;
            $targetPath = $persistPath . '/' . $file;
            if(in_array($file, $ignore)) {
                continue; //and do not log
            }
            if(!file_exists($filePath)) {
                continue; // strange but found this for a client..., on windows: HS ID 711715228 
            }
            if(   (!ShortPixel::isProcessable($file) && !is_dir($filePath))
                || isset($dataArr[$file]) && $dataArr[$file]->status == 'deleted'
                || isset($dataArr[$file])
                && (  $dataArr[$file]->status == 'success' && !$this->isChanged($dataArr[$file], $file, $persistPath, $path)
                    || $dataArr[$file]->status == 'skip') ) {
                if(!isset($dataArr[$file]) || $dataArr[$file]->status !== 'success')
                    $this->logger->logFirst($filePath, SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - SKIPPING $path/$file - status " . (isset($dataArr[$file]) ? $dataArr[$file]->status : "not processable"));
                continue;
            }
            //if retried too many times recently {
            if(isset($dataArr[$file]) && $dataArr[$file]->status == 'pending') {
                $retries = $dataArr[$file]->retries;
                //over 3 retries wait a minute for each, over 5 retries 2 min. for each, over 10 retries 5 min for each, over 10 retries, 10 min. for each.
                $delta = max(0, $retries - 2) * 60 + max(0, $retries - 5) * 60 + max(0, $retries - 10) * 180 + max(0, $retries - 20) * 450;
                if($dataArr[$file]->changeDate > time() - $delta) {
                    $filesWaiting++;
                    $this->logger->logFirst($filePath, SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - TOO MANY RETRIES for $file");
                    continue;
                }
            }
            if(is_dir($filePath)) {
                if($recurseDepth <= 0) continue;
                if(!isset($dataArr[$file])) {
                    $dataArr[$file] = $this->newMeta($filePath);
                    $dataArr[$file]->filePos = $this->appendMeta($dataArr[$file], $fp);
                }
                $resultsSubfolder = $this->cache->fetch($filePath);
                if(!$resultsSubfolder) {
                    $resultsSubfolder =  $this->getTodo($filePath, $count, $exclude, $targetPath, $maxTotalFileSizeMb, $recurseDepth - 1);
                    if(!count($resultsSubfolder->files)) {
                        //cache the folders with nothing to do.
                        $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - Nothing to do for: $filePath. Caching.");
                        $this->cache->store($filePath, $resultsSubfolder);
                    }
                } else {
                    $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - Cache says nothing to do for: $filePath");
                }
                if(count($resultsSubfolder->files)) {
                    return $resultsSubfolder;
                }  elseif($dataArr[$file]->status != 'success' && !$resultsSubfolder->filesWaiting) {//otherwise ignore the folder but mark it as succeeded;
                    $dataArr[$file]->status = 'success';
                    $this->updateMeta($dataArr[$file], $fp);
                }
            } else {
                $toUpdate = false; //will defer updating the record only if we finally add the image (if the image is too large for this set will not add it in the end
                clearstatcache(true, $targetPath);
                if(isset($dataArr[$file])) {
                    if(    ($dataArr[$file]->status == 'success')
                        && (filesize($targetPath) !== $dataArr[$file]->optimizedSize)) {
                        // a file with the wrong size
                        $dataArr[$file]->status = 'pending';
                        $dataArr[$file]->optimizedSize = 0;
                        $dataArr[$file]->changeDate = time();
                        $toUpdate = true;
                        if(time() - strtotime($dataArr[$file]->changeDate) < 1800) { //need to refresh the file processing on the server
                            $this->updateMeta($dataArr[$file], $fp);
                            return (object)array('files' => array($filePath), 'filesPending' => array(), 'filesWaiting' => 0, 'refresh' => true);
                        }
                    }
                    elseif($dataArr[$file]->status == 'error') {
                        if($dataArr[$file]->retries >= ShortPixel::MAX_RETRIES) {
                            $dataArr[$file]->status = 'skip';
                            $this->updateMeta($dataArr[$file], $fp);
                            continue;
                        } else {
                            $dataArr[$file]->retries += 1;
                            $toUpdate = true;
                        }
                    }

                    elseif($dataArr[$file]->status == 'pending' && preg_match("/http[s]{0,1}:\/\/" . Client::API_DOMAIN() . "/", $dataArr[$file]->message)) {
                        //elseif($dataArr[$file]->status == 'pending' && strpos($dataArr[$file]->message, str_replace("https://", "http://",\ShortPixel\Client::API_URL())) === 0) {
                        //the file is already uploaded and the call should  be made with the existent URL on the optimization server
                        $apiURL = $dataArr[$file]->message;
                        $pendingURLs[$apiURL] = $filePath;
                    }
                }
                elseif(!isset($dataArr[$file])) {
                    $dataArr[$file] = $this->newMeta($filePath);
                    $dataArr[$file]->filePos = $this->appendMeta($dataArr[$file], $fp);
                }

                clearstatcache(true, $filePath);
                if(filesize($filePath) + $totalFileSize > $maxTotalFileSize){
                    if(filesize($filePath) > $maxTotalFileSize) { //skip this as it won't ever be selected with current settings
                        $dataArr[$file]->status = 'skip';
                        if(filesize($filePath) > ShortPixel::CLIENT_MAX_BODY_SIZE * pow(1024, 2)) {
                            $dataArr[$file]->retries = 99;
                        }
                        $dataArr[$file]->message = 'File larger than the set limit of ' . $maxTotalFileSizeMb . 'MBytes';
                        $this->updateMeta($dataArr[$file], $fp); //this one is too big, we skipped it, just continue with next.
                    }
                    continue; //the total file size would exceed the limit so leave this image out for now. If it's not too large by itself, will take it in the next pass.
                }
                if($toUpdate) {
                    $this->updateMeta($dataArr[$file], $fp);
                }
                $results[] = $filePath;
                $totalFileSize += filesize($filePath);
                $remain--;

                if($remain <= 0) {
                    break;
                }
            }
        }

        return (object)array('files' => $results, 'filesPending' => $pendingURLs, 'filesWaiting' => $filesWaiting, 'refresh' => false);
    }
        /**
     * @param $data - the .shortpixel metadata
     * @param $file - the file basename
     * @param $persistPath - the target path for the optimized files and for the .shortpixel metadata
     * @param $sourcePath - the path of the original images
     * @return bool true if the image is optimized but needs to be reoptimized because it changed
     */
    protected function isChanged($data, $file, $persistPath, $sourcePath ) {
        clearstatcache(true, $sourcePath);
        return $persistPath === $sourcePath && filesize($sourcePath . '/' . $file) != $data->optimizedSize
            || $persistPath !== $sourcePath && $data->originalSize > 0 && filesize($sourcePath . '/' . $file) != $data->originalSize;
    }

    function getNextTodo($path, $count)
    {
        // TODO: Implement getNextTodo() method.
    }

    function doneGet()
    {
        // TODO: Implement doneGet() method.
    }

    function getOptimizationData($path)
    {
        // TODO: Implement getOptimizationData() method.
    }

    function setPending($path, $optData) {
        return $this->setStatus($path, $optData, 'pending');
    }

    function setOptimized($path, $optData = array()) {
        return $this->setStatus($path, $optData, 'success');
    }

    function setFailed($path, $optData) {
        return $this->setStatus($path, $optData, 'error');
    }

    function setSkipped($path, $optData) {
        return $this->setStatus($path, $optData, 'skip');
    }

    protected function setStatus($path, $optData, $status) {
        $toClose = $this->openMetaFileIfNeeded(dirname($path));
        $fp = $this->getMetaFile(dirname($path));

        $meta = $this->findMeta($path);
        if($meta) {
            $meta->retries++;
            $meta->changeDate = time();
        } else {
            $meta = $this->newMeta($path);
        }
        $meta->status = $status == 'error' ? $meta->retries > ShortPixel::MAX_RETRIES ? 'skip' : 'pending' : $status;
        $metaArr = array_merge((array)$meta, $optData);
        if(isset($meta->filePos)) {
            $this->updateMeta((object)$metaArr, $fp, false);
        } else {
            $this->appendMeta((object)$metaArr, $fp, false);
        }

        if($toClose) {
            $this->closeMetaFile(dirname($path));
        }
        return $meta->status;
    }

    protected function openMetaFileIfNeeded($path) {
        if(isset($this->fp[$path])) {
            fseek($this->fp[$path], 0);
            return false;
        }
        $fp = $this->openMetaFile($path);
        if(!$fp) {
            throw new \Exception("Could not open meta file in folder " . $path . ". Please check permissions.", -14);
        }
        $this->fp[$path] = $fp;
        return true;
    }

    protected function getMetaFile($path) {
        return $this->fp[$path];
    }

    protected function closeMetaFile($path) {
        if(isset($this->fp[$path])) {
            $fp = $this->fp[$path];
            unset($this->fp[$path]);
            fclose($fp);
        }
    }

    protected function readMetaFile($fp) {
        $dataArr = array(); $err = false;
        for ($i = 0; ($line = fgets($fp)) !== FALSE; $i++) {
            $data = $this->parse($line);
            if($data) {
                $data->filePos = $i;
                if(isset($dataArr[$data->file])) {
                    $err = true; //found situations where a line was duplicated, will rewrite but take only the first
                } else {
                    $dataArr[$data->file] = $data;
                }
            } else {
                $err = true;
            }
        }
        if($err) { //at least one error found in the .shortpixel file, rewrite it
            fseek($fp, 0);
            ftruncate($fp, 0);
            foreach($dataArr as $meta) {
                fwrite($fp, $this->assemble($meta));
                fwrite($fp, $line . "\r\n");
            }
        }
        return $dataArr;
    }

    protected function openMetaFile($path, $type = 'update') {
        $metaFile = $path . '/' . ShortPixel::opt("persist_name");
        if(!is_dir($path) && !@mkdir($path, 0777, true)) { //create the folder
            throw new ClientException("The metadata destination path cannot be found. Please check rights", -17);
        }
        $fp = @fopen($metaFile, $type == 'update' ? 'c+' : 'r');
        if(!$fp) {
            if(is_dir($metaFile)) { //saw this for a client
                throw new ClientException("Could not open persistence file $metaFile. There's already a directory with this name.", -16);
            } else {
                throw new ClientException("Could not open persistence file $metaFile. Please check rights.", -16);
            }
        }
        return $fp;
    }

    protected function findMeta($path) {
        $fp = $this->openMetaFile(dirname($path));
        fseek($fp, 0);
        for ($i = 0; ($line = fgets($fp)) !== FALSE; $i++) {
            $data = $this->parse($line);
            if($data->file === \MediaCloud\Vendor\ShortPixel\MB_basename($path)) {
                $data->filePos = $i;
                return $data;
            }
        }
        return false;
    }

    /**
     * @param $meta
     * @param bool|false $returnPointer - set this to true if need to have the file pointer back afterwards, such as when updating while reading the file line by line
     */
    protected function updateMeta($meta, $fp, $returnPointer = false) {
        if($returnPointer) {
            $crt = ftell($fp);
        }
        fseek($fp, self::LINE_LENGTH * $meta->filePos); // +2 for the \r\n
        fwrite($fp, $this->assemble($meta));
        fflush($fp);
        if($returnPointer) {
            fseek($fp, $crt);
        }
    }

    /**
     * @param $meta
     * @param bool|false $returnPointer - set this to true if need to have the file pointer back afterwards, such as when updating while reading the file line by line
     */
    protected function appendMeta($meta, $fp, $returnPointer = false) {
        if($returnPointer) {
            $crt = ftell($fp);
        }
        $fstat = fstat($fp);
        fseek($fp, 0, SEEK_END);
        $line = $this->assemble($meta);
        //$ob = $this->parse($line);
        fwrite($fp, $line . "\r\n");
        fflush($fp);
        if($returnPointer) {
            fseek($fp, $crt);
        }
        return $fstat['size'] / self::LINE_LENGTH;
    }

    protected function newMeta($file) {
        //$this->logger->log(SPLog::PRODUCER_PERSISTER, "newMeta: file $file exists? " . (file_exists($file) ? "Yes" : "No"));
        return (object) array(
            "type" => is_dir($file) ? 'D' : 'I',
            "status" => 'pending',
            "retries" => 0,
            "compressionType" => $this->options['lossy'] == 1 ? 'lossy' : ($this->options['lossy'] == 2 ? 'glossy' : 'lossless'),
            "keepExif" => $this->options['keep_exif'],
            "cmyk2rgb" => $this->options['cmyk2rgb'],
            "resize" => $this->options['resize_width'] ? 1 : 0,
            "resizeWidth" => 0 + $this->options['resize_width'],
            "resizeHeight" => 0 + $this->options['resize_height'],
            "convertto" => $this->options['convertto'],
            "percent" => null,
            "optimizedSize" => null,
            "changeDate" => time(),
            "file" => \MediaCloud\Vendor\ShortPixel\MB_basename($file),
            "message" => '',
            //file does not exist if source is a WebFolder and the optimized images are saved to a different target
            "originalSize" => is_dir($file) || !file_exists($file) ? 0 : filesize($file));
    }

    const LINE_LENGTH = 465; //including the \r\n at the end

    protected function parse($line) {
        if(strlen(rtrim($line, "\r\n")) != (self::LINE_LENGTH - 2)) return false;
        $percent = trim(substr($line, 52, 6));
        $optimizedSize = trim(substr($line, 58, 9));
        $originalSize = trim(substr($line, 454, 9));
        $ret = (object) array(
            "type" => trim(substr($line, 0, 2)),
            "status" => trim(substr($line, 2, 11)),
            "retries" => trim(substr($line, 13, 2)),
            "compressionType" => trim(substr($line, 15, 9)),
            "keepExif" => trim(substr($line, 24, 2)),
            "cmyk2rgb" => trim(substr($line, 26, 2)),
            "resize" => trim(substr($line, 28, 2)),
            "resizeWidth" => trim(substr($line, 30, 6)),
            "resizeHeight" => trim(substr($line, 36, 6)),
            "convertto" => trim(substr($line, 42, 10)),
            "percent" => is_numeric($percent) ? floatval($percent) : 0.0,
            "optimizedSize" => is_numeric($optimizedSize) ? intval($optimizedSize) : 0,
            "changeDate" => strtotime(trim(substr($line, 67, 20))),
            "file" => rtrim(substr($line, 87, 256)), //rtrim because there could be file names starting with a blank!! (had that)
            "message" => trim(substr($line, 343, 111)),
            "originalSize" => is_numeric($originalSize) ? intval($originalSize) : 0,
        );
        if(!in_array($ret->status, self::$ALLOWED_STATUSES) || !$ret->changeDate) {
            return false;
        }
        return $ret;
    }

    protected function assemble($data) {
        return sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s",
            str_pad($data->type, 2),
            str_pad($data->status, 11),
            str_pad($data->retries % 100, 2), // for folders, retries can be > 100 so do a sanity check here - we're not actually interested in folder retries
            str_pad($data->compressionType, 9),
            str_pad($data->keepExif, 2),
            str_pad($data->cmyk2rgb, 2),
            str_pad($data->resize, 2),
            str_pad(substr($data->resizeWidth, 0 , 5), 6),
            str_pad(substr($data->resizeHeight, 0 , 5), 6),
            str_pad($data->convertto, 10),
            str_pad(substr(number_format($data->percent, 2, ".",""),0 , 5), 6),
            str_pad(substr(number_format($data->optimizedSize, 0, ".", ""),0 , 8), 9),
            str_pad(date("Y-m-d H:i:s", $data->changeDate), 20),
            str_pad(substr($data->file, 0, 255), 256),
            str_pad(substr($data->message, 0, 110), 111),
            str_pad(substr(number_format($data->originalSize, 0, ".", ""),0 , 8), 9)
        );
    }
}
