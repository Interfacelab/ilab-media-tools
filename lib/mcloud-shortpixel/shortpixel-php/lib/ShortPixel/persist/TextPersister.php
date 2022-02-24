<?php
/**
 * User: simon
 * Date: 19.08.2016
 * Time: 18:05
 */

namespace MediaCloud\Vendor\ShortPixel\persist;
use MediaCloud\Vendor\ShortPixel\ClientException;
use MediaCloud\Vendor\ShortPixel\Lock;
use MediaCloud\Vendor\ShortPixel\notify\ProgressNotifierFileQ;
use \MediaCloud\Vendor\ShortPixel\Persister;
use MediaCloud\Vendor\ShortPixel\Settings;
use \MediaCloud\Vendor\ShortPixel\ShortPixel;
use \MediaCloud\Vendor\ShortPixel\Client;
use \MediaCloud\Vendor\ShortPixel\SPCache;
use MediaCloud\Vendor\ShortPixel\SPLog;

/**
 * Class TextPersister - save the optimization information in .shortpixel files in the current folder of the images
 * @package MediaCloud\Vendor\ShortPixel\persist
 */
class TextPersister implements Persister {

    private $options;
    private $logger;
    private $cache;

    const FLAG_WEBP = 2;//second bit set like in "10"
    const FLAG_AVIF = 32;//6th bit set like in "100000"

    function __construct($options)
    {
        $this->options = $options;
        $this->logger = SPLog::Get(SPLog::PRODUCER_PERSISTER);
        $this->cache = SPCache::Get();
    }

    public static function IGNORED_BY_DEFAULT() {
        return array('.','..',ShortPixel::opt('persist_name'),Settings::FOLDER_INI_NAME,Lock::FOLDER_LOCK_FILE,ProgressNotifierFileQ::PROGRESS_FILE_NAME,'ShortPixelBackups');
    }

    function isOptimized($path)
    {
        if(!file_exists($path)) {
            return false;
        }
        try {
            $toClose = !TextMetaFile::IsOpen(dirname($path), 'read');
            $metaFile = TextMetaFile::Get(dirname($path));
            $metaData = TextMetaFile::find($path);
            if($toClose) {
                $metaFile->close();
            }
            return isset($metaData->file);
        } catch(ClientException $cx) {
            return false;
        }

        return false;
    }

    protected function ignored($exclude) {
        $optExclude = isset($this->options['exclude']) && $this->options['exclude']
            ? (is_array($this->options['exclude'])
                ? $this->options['exclude']
                : (is_string($this->options['exclude']) ? explode(',', $this->options['exclude']) : array()))
            : array();
        $optExclude = array_map('trim', $optExclude);
        return array_values(array_merge(self::IGNORED_BY_DEFAULT(), is_array($exclude) ? $exclude : arrray(), $optExclude));
    }

    static function sanitize($filename) {
        //print_r($filename);die();
        // our list of "unsafe characters", add/remove characters if necessary
        $dangerousCharacters = array("\n", "\r", "\\", "\b");
        // every forbidden character is replaced by a space
        $safe_filename = str_replace($dangerousCharacters, ' ', $filename, $count);

        return $safe_filename;
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
                    $toClose = !TextMetaFile::IsOpen($persistPath);
                    $metaFile = TextMetaFile::Get($persistPath);
                    $dataArr = $metaFile->readAll();
                } catch(ClientException $e) {
                    if(!isset($metaFile) || is_null($metaFile) && is_dir($persistPath) && file_exists($persistPath . '/' . ShortPixel::opt("persist_name"))) {
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
                        rename($path . '/' . $file, $path . '/' . self::sanitize($file));
                        $info->total++;
                        if(!isset($dataArr[$file]) || $dataArr[$file]->status == 'pending') {
                            $info->pending++;
                            $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->info - PENDING STATUS: $path/$file");
                        }
                        elseif(   $dataArr[$file]->status == 'success' && $this->isChanged($dataArr[$file], $file, $persistPath, $path)
                            // || ($dataArr[$file]->status == 'skip' &&  ($dataArr[$file]->retries <= ShortPixel::MAX_RETRIES || $retrySkipped))) {
                               || ($dataArr[$file]->status == 'skip' && $retrySkipped)) {
                            if($dataArr[$file]->status == 'skip' && $retrySkipped) {
                                $dataArr[$file]->retries = 0;
                            } elseif($persistPath !== $path) {
                                //ORIGINAL image size is changed, also update the size
                                $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->info - CHANGED ("
                                    . ($dataArr[$file]->originalSize > 0 ? " original size: " . filesize($path . '/' . $file) . ", persisted size: " . $dataArr[$file]->originalSize : '') . ") - REVERT TO PENDING: $path/$file");
                                $dataArr[$file]->originalSize = filesize($path . '/' . $file);
                            }
                            //file changed since last optimized, mark it as pending
                            $dataArr[$file]->status = 'pending';
                            $metaFile->update($dataArr[$file]);
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
                    $info->todo = $this->getTodoInternal($files, $dataArr, $metaFile, $path, 1, $exclude, $persistPath, ShortPixel::CLIENT_MAX_BODY_SIZE, $recurseDepth);
                }
            }
            else {
                if(!file_exists($persistPath)) {
                    throw new ClientException("File not found: $persistPath", -15);
                }
                $persistFolder = dirname($persistPath);
                $meta = $toClose = false;
                try {
                    $toClose = !TextMetaFile::IsOpen($persistFolder, 'read');
                    $meta = TextMetaFile::find($persistPath);
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
        if($toClose && !is_null($metaFile)) {
            $metaFile->close();
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

        $toClose = !TextMetaFile::IsOpen($persistPath);
        $metaFile = TextMetaFile::Get($persistPath);

        $files = scandir($path);
        $dataArr = $metaFile->readAll();

        $ret = $this->getTodoInternal($files, $dataArr, $metaFile, $path, $count, $exclude, $persistPath, $maxTotalFileSizeMb, $recurseDepth);

        if($toClose) {
            $metaFile->close();
        }

        if(count($ret->files) + count($ret->filesPending) + $ret->filesWaiting == 0) {
            $this->logger->logFirst($path, SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - FOR $path RETURN NONE");
        } else {
            $this->logger->clearLogged(SPLog::PRODUCER_PERSISTER, $path);
            $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - FOR $path RETURN", $ret);
        }
        return $ret;
    }


    /**
     * @param $files
     * @param $dataArr
     * @param TextMetaFile $metaFile
     * @param $path
     * @param $count
     * @param $exclude
     * @param $persistPath
     * @param $maxTotalFileSizeMb
     * @param $recurseDepth
     * @return object
     */
    protected function getTodoInternal(&$files, &$dataArr, $metaFile, $path, $count, $exclude, $persistPath, $maxTotalFileSizeMb, $recurseDepth)
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
            if(  !is_dir($filePath) //never skip folders whatever reason as they can have changes inside them
               //that's a file:
               &&(   !ShortPixel::isProcessable($file) //either the file is not processable
                  || isset($dataArr[$file]) && $dataArr[$file]->status == 'deleted' //or it's deleted
                  || isset($dataArr[$file])
                     && (  $dataArr[$file]->status == 'success' && !$this->isChanged($dataArr[$file], $file, $persistPath, $path) //or changed
                        || $dataArr[$file]->status == 'skip') ) ) //or skipped
            {
                if(!isset($dataArr[$file]) || $dataArr[$file]->status !== 'success') {
                    $this->logger->logFirst($filePath, SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - SKIPPING $path/$file - status " . (isset($dataArr[$file]) ? $dataArr[$file]->status : "not processable"));
                }
                continue;
            }

            if(isset($dataArr[$file]) && $this->isChanged($dataArr[$file], $file, $persistPath, $path)) {
                //This means the image was externally changed, revert it to pending state and update the size.
                $currentSize = filesize($path . '/' . $file);
                $this->logger->log( SPLog::PRODUCER_PERSISTER, "FILE OPTIMIZED BUT CHANGED AFTERWARDS: $file - initial size: " . $dataArr[$file]->originalSize . " current: " . $currentSize);
                $dataArr[$file]->status = 'pending';
                $dataArr[$file]->originalSize =  $currentSize;
                $metaFile->update($dataArr[$file]);
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
                    $dataArr[$file] = TextMetaFile::newEntry($filePath, $this->options);
                    $dataArr[$file]->filePos = $metaFile->append($dataArr[$file]);
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
                    $metaFile->update($dataArr[$file]);
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
                            $metaFile->update($dataArr[$file]);
                            return (object)array('files' => array($filePath), 'filesPending' => array(), 'filesWaiting' => 0, 'refresh' => true);
                        }
                    }
                    elseif($dataArr[$file]->status == 'error') {
                        if($dataArr[$file]->retries >= ShortPixel::MAX_RETRIES) {
                            $dataArr[$file]->status = 'skip';
                            $metaFile->update($dataArr[$file]);
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
                    $dataArr[$file] = TextMetaFile::newEntry($filePath, $this->options);
                    $dataArr[$file]->filePos = $metaFile->append($dataArr[$file]);
                }

                clearstatcache(true, $filePath);
                $fsz = filesize($filePath);
                if($fsz + $totalFileSize > $maxTotalFileSize){
                    if($fsz > $maxTotalFileSize) { //skip this as it won't ever be selected with current settings
                        $dataArr[$file]->status = 'skip';
                        if(filesize($filePath) > ShortPixel::CLIENT_MAX_BODY_SIZE * pow(1024, 2)) {
                            $dataArr[$file]->retries = 99;
                        }
                        $dataArr[$file]->message = 'File larger than the set limit of ' . $maxTotalFileSizeMb . 'MBytes';
                        $this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - File too large: $path/$file - size: " . $fsz . "MBytes");
                        $metaFile->update($dataArr[$file]); //this one is too big, we skipped it, just continue with next.
                    } else {
                        //$this->logger->log(SPLog::PRODUCER_PERSISTER, "TextPersister->getTodo - File won't fit this round: $path/$file - size: " . $fsz . "MBytes");
                    }
                    continue; //the total file size would exceed the limit so leave this image out for now. If it's not too large by itself, will take it in the next pass.
                }
                if($toUpdate) {
                    $metaFile->update($dataArr[$file]);
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
        $fileSize = filesize($sourcePath . '/' . $file);
        return $persistPath === $sourcePath && $data->optimizedSize > 0 && $fileSize != $data->optimizedSize
            || $persistPath !== $sourcePath && $data->originalSize > 0  && $fileSize != $data->originalSize;
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
        $folder = dirname($path);
        $toClose = !TextMetaFile::IsOpen($folder);
        $meta = TextMetaFile::Get($folder);

        $metaData = TextMetaFile::find($path, 'update');
        if($metaData) {
            $metaData->retries++;
            $metaData->changeDate = time();
        } else {
            $metaData = TextMetaFile::newEntry($path, $this->options);
        }
        $metaData->status = $status == 'error' ? $metaData->retries > ShortPixel::MAX_RETRIES ? 'skip' : 'pending' : $status;
        $metaArr = array_merge((array)$metaData, $optData);
        if(isset($metaData->filePos)) {
            $meta->update((object)$metaArr, false);
        } else {
            $meta->append((object)$metaArr, false);
        }

        if($toClose) {
            $meta->close();
        }
        return $metaData->status;
    }

}
