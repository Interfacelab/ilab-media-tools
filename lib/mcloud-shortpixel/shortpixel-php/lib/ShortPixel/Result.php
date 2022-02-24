<?php

namespace MediaCloud\Vendor\ShortPixel;
use MediaCloud\Vendor\ShortPixel\notify\ProgressNotifier;

/**
 * Class Result - handles the result of the optimization (saves to file or returns a buffer, etc)
 * @package ShortPixel
 */
class Result {
    protected $commander, $ctx, $logger;

    public function __construct($commander, $context) {
        $this->commander = $commander;
        $this->ctx = $context;
        $this->logger = SPLog::Get(SPLog::PRODUCER_RESULT);
    }

    /**
     * returns the metadata provided by the optimizer
     * @return mixed
     */
    public function ctx() {
        return $this->ctx;
    }

    public function toBuffer() {
        return $this->ctx;
    }

    /**
     * @param null $path - path to save the file to
     * @param null $fileName - filename of the saved file. If it's an array, then one entry for each optimized file, in the same order.
     * @param null $bkPath - the path to save a backup of the original file
     * @return object containig lists with succeeded, pending, failed and same items (same means the image did not need optimization)
     * @throws AccountException
     * @throws ClientException
     */
    public function toFiles($path = null, $fileName = null, $bkPath = null) {
//        echo(" PATH: $path BkPath: $bkPath");
//        spdbgd($this->ctx, 'context');
        $thisDir = str_replace(DIRECTORY_SEPARATOR, '/', (getcwd() ? getcwd() : __DIR__));

        if($path) {
            $path = rtrim($path, '/\\');
            if(   (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && preg_match('/^[a-zA-Z]:(\/|\\\)/', $path) === 0) //it's Windows and no drive letter X:
               || (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && substr($path, 0, 1) !== '/')) { //it's not Windows and doesn't start with a /
                $path = SPTools::trailingslashit(ShortPixel::opt("base_path") ?: $thisDir) . $path;
            }
        }
        if(!$bkPath && ShortPixel::opt("backup_path")) {
            $bkPath = ShortPixel::opt("backup_path");
        }
        if($bkPath && strpos($bkPath,'/') !== 0) { //it's a relative path
            $bkPath = normalizePath($path . '/' . $bkPath);
        }
        $i = 0;
        $succeeded = $pending = $failed = $same = array();

        $cmds = array_merge(ShortPixel::options(), $this->commander->getCommands());

        while(true) {
            $items = $this->ctx->body;
            if(!is_array($items) || count($items) == 0) {
                throw new AccountException("Result received no items to save!", -1);
//                return (object)array( 'status' => array('code' => 2, 'message' => 'Folder completely optimized'));
            }
            //check API key errors
            if(isset($items->Status->Code) && $items->Status->Code < 0) {
                throw new AccountException($items->Status->Message, $items->Status->Code);
            }
            // No API level error
            $retry = false;
            foreach($items as $item) {

                $targetPath = $path; $originalPath = $baseUrl = false;
                $this->logger->log(SPLog::PRODUCER_RESULT, "RESULT toFiles while CTX:", $this->ctx->fileMappings);

                if($this->ctx->fileMappings && count($this->ctx->fileMappings)) { // it was optimized from a local file, fileMappings contains the mappings from the local files to the internal ShortPixel URLs
                    $originalPath = isset($this->ctx->fileMappings[$item->OriginalURL]) ? $this->ctx->fileMappings[$item->OriginalURL] : false;
                    //
                    $this->logger->log(SPLog::PRODUCER_RESULT, "ORIGINAL PATH:" . $originalPath);
                    if(ShortPixel::opt("base_source_path") && $originalPath) {
                        $origPathParts = explode('/', str_replace(ShortPixel::opt("base_source_path"). "/", "", $originalPath));
                        $origFileName = $origPathParts[count($origPathParts) - 1];
                        unset($origPathParts[count($origPathParts) - 1]);
                        $relativePath = implode('/', $origPathParts);
                    } elseif($originalPath) {
                        $origPathParts = explode('/', $originalPath);
                        $origFileName = $origPathParts[count($origPathParts) - 1];
                        $relativePath = "";
                    } elseif(isset($item->OriginalFileName)) {
                        $origFileName = $item->OriginalFileName;
                        $relativePath = "";
                    } else {
                        throw new ClientException("Cannot determine a filename to save to." . $item->OriginalURL . " CTX: " . json_encode($this->ctx->fileMappings));
                    }
                } elseif(isset($item->OriginalURL)) {  // it was optimized from a URL
                    $baseUrl = ShortPixel::opt("base_url");
                    if($baseUrl && strlen($baseUrl)) {
                        $origURLParts = explode('/', trim(rawurldecode(str_replace($baseUrl, "", $item->OriginalURL)), '/'));
                        $origFileName = $origURLParts[count($origURLParts) - 1];
                        unset($origURLParts[count($origURLParts) - 1]);
                        $relativePath = implode('/', $origURLParts);
                    } else {
                        $origURLParts = explode('/', $item->OriginalURL);
                        $origFileName = $origURLParts[count($origURLParts) - 1];
                        $relativePath = "";
                    }
                    if(ShortPixel::opt('url_filter') == 'encode') {
                        $extPos = strrpos($origFileName, ".");
                        $ext = substr($origFileName,$extPos + 1);
                        $origFileName = substr($origFileName, 0, $extPos);
                        $origFileName = urldecode(base64_decode($origFileName)) . '.' . $ext;
                    }
                    $originalPath = SPTools::trailingslashit(ShortPixel::opt("base_source_path")) . (strlen($relativePath) ? $relativePath . '/' : '') . $origFileName;
                } else { // something is wrong
                    throw(new ClientException("Malformed response. Please contact support."));
                }
                if(!$targetPath) { //se pare ca trebuie oricum
                    $targetPath = SPTools::trailingslashit(ShortPixel::opt("base_path") ?: $thisDir) . $relativePath;
                } elseif(ShortPixel::opt("base_source_path") && strlen($relativePath)) {
                    $targetPath = SPTools::trailingslashit($targetPath) . $relativePath;
                }

                if($fileName) {
                    if(is_array($fileName)) {
                        if(!isset($fileName[$i])) {
                            throw new ClientException('Names array contains less names than the files sent to optimization');
                        }
                        $fn = $fileName[$i];
                    } else {
                        if($i > 0) {
                            $dotExt = '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                            $fn = basename($fileName, $dotExt) . "_" . $i . $dotExt;
                        } else {
                            $fn = $fileName;
                        }
                    }
                } else {
                    $fn = $origFileName;
                }
                $target = $targetPath . '/' . $fn;

                if($originalPath) {
                    $item->OriginalFile = $originalPath;
                    if(!mb_detect_encoding($originalPath, 'UTF-8', true)) { $item->OriginalFileUTF8 = utf8_encode($originalPath); }
                }
                $item->SavedFile = $target;
                if(!mb_detect_encoding($target, 'UTF-8', true)) { $item->SavedFileUTF8 = utf8_encode($target); }

                //TODO: that one is a hack until the API waiting bug is fixed. Afterwards, just throw an exception
                if(    $item->Status->Code == 2
                    && (   $item->LossySize == 0  && $item->LoselessSize == 0
                        || $item->WebPLossyURL != 'NA' && ($item->WebPLossySize == 'NA' || !$item->WebPLossySize )
                        || $item->WebPLosslessURL != 'NA' && ($item->WebPLosslessSize == 'NA' || !$item->WebPLosslessSize)
                        || $item->AVIFLossyURL != 'NA' && ($item->AVIFLossySize == 'NA' || !$item->AVIFLossySize )
                        || $item->AVIFLosslessURL != 'NA' && ($item->AVIFLosslessSize == 'NA' || !$item->AVIFLosslessSize))) {
                    $item->Status->Code = 1;
                }

                if($item->Status->Code == 1) {
                    $found = $this->findItem($item, $pending, "OriginalURL");
                    if($found === false) {
                        $item->Retries = 1;
                        $pending[] = $item;
                        $this->persist($item, $cmds, 'pending');
                    } else {
                        $pending[$found]->Retries += 1;
                    }
                    $this->logger->log(SPLog::PRODUCER_RESULT, "PENDING, RETRIES:", $pending[$found]->Retries);
                    continue;
                }
                elseif ($item->Status->Code != 2) {
                    $this->removeItem($item, $pending, "OriginalURL");
                    if($item->Status->Code == -113) {
                        throw new AccountException("Too many inaccessible URLs from the same domain, please check accessibility and try again.", "-113");
                    }
                    if($item->Status->Code == -102 || $item->Status->Code == -106) {
                        // -102 is expired, means we need to resend the image through post
                        // -106 is file was not downloaded due to access restrictions - if these are uploaded files it looks like a bug in the API
                        //      TODO find and fix
                        if(isset($this->ctx->fileMappings[$item->OriginalURL])) {
                            unset($this->ctx->fileMappings[$item->OriginalURL]);
                        }
                        $item->OriginalURL = false;
                    }

                    if($item->Status->Code == -201 || $item->Status->Code == -202) { //unrecoverable, no need to retry
                        $st = 'skip';
                    } else { //will persist as 'pending' if retries < MAX_RETRIES
                        $st = 'error';
                    }
                    $status = $this->persist($item, $cmds, $st);
                    if($status == 'pending') {
                        $retry = true;
                    } else {
                        $failed[] = $item;
                        $this->commander->isDone($item);
                        $this->removeItem($item, $pending, "OriginalURL");
                    }
                    $this->logger->log(SPLog::PRODUCER_RESULT, "FAILED WITH CODE:", $item->Status->Code);
                    continue;
                }
                //IF file is locally accessible, the source file size should be the same as the size downloaded by (or posted to) ShortPixel servers
                elseif(clearstatcache(true, $originalPath) || file_exists($originalPath) && filesize($originalPath) != $item->OriginalSize) {
                    $item->Status->Code = -110;
                    $item->Status->Message = "Wrong original size. Expected (local source file): " . filesize($originalPath) . " downloaded by ShortPixel: " . $item->OriginalSize;
                    $status = $this->persist($item, $cmds, 'error');
                    if($status == 'pending') {
                        $retry = true;
                    } else {
                        $failed[] = $item;
                        $this->commander->isDone($item);
                        $this->removeItem($item, $pending, "OriginalURL");
                    }
                    $this->logger->log(SPLog::PRODUCER_RESULT, "ERROR:", $item->Status->Message);
                    continue;
                }
                elseif($item->PercentImprovement == 0) {
                    //sometimes the percent is 0 and the size is different (by some octets) so put the correct size in place
                    if(file_exists($originalPath)) {
                        clearstatcache(true, $originalPath);
                        if($cmds["lossy"] > 0) {
                            $item->LossySize = filesize($originalPath);
                        } else {
                            $item->LoselessSize = filesize($originalPath);
                        }
                        if(!file_exists($target)) {
                            //this is a case when the target is different from the original path, so just copy the file over
                            @copy($originalPath, $target);
                        }
                    }
                    $this->checkSaveWebP($item, $target, $cmds);
                    $this->checkSaveAVIF($item, $target, $cmds);
                    $same[] = $item;
                    $this->removeItem($item, $pending, "OriginalURL");
                    $this->persist($item, $cmds);
                    $this->commander->isDone($item);
                    $this->logger->log(SPLog::PRODUCER_RESULT, "IMPROVEMENT 0");
                    continue;
                }

                if(!is_dir($targetPath) && !@mkdir($targetPath, 0777, true)) { //create the folder
                    throw new ClientException("The destination path $targetPath cannot be found.");
                }

                //Now that's an optimized image indeed
                try {
                    $bkCrtFilePath = false;
                    if($bkPath && $originalPath) {
                        $bkCrtPath = rtrim($bkPath, '/') . '/' . (strlen($relativePath) ? $relativePath . '/' : '');
                        if(!is_dir($bkCrtPath) && !@mkdir($bkCrtPath, 0777, true)) {
                            throw new Exception("Cannot create backup folder " . $bkCrtPath, -1);
                        }
                        $bkCrtFilePath = $bkCrtPath . MB_basename($originalPath);
                        if(!file_exists($bkCrtFilePath)) { //just make sure we're not overwriting the original in any case
                            if (!copy($originalPath, $bkCrtFilePath) && !file_exists($bkCrtFilePath)) {
                                throw new Exception("Cannot copy to backup folder " . $bkCrtPath, -1);
                            }
                        }
                    }

                    //try to figure out a backup URL
                    if($baseUrl) {
                        if($originalPath !== $targetPath) {
                            $item->BackupURL = $item->OriginalURL;
                        } elseif($bkCrtFilePath) {
                            $deltaPos = strspn($path ^ $bkPath, "\0");
                            $delta = substr($path, $deltaPos);
                            $bkDelta = substr($bkPath, $deltaPos);
                            if(strpos($baseUrl, $delta) > 0) {
                                //only in this case there can be a backup URL (the backup path is inside the web root)
                                $item->BackupURL = str_replace($delta, $bkDelta, $baseUrl) . '/' . (strlen($relativePath) ? $relativePath . '/' : '') . MB_basename($originalPath);
                            }
                        }
                    }

                    $optURL = $cmds["lossy"] > 0 ? $item->LossyURL : $item->LosslessURL; //this works also for glossy (2)
                    $optSize = $cmds["lossy"] > 0 ? $item->LossySize : $item->LoselessSize;

                    $downloadOK = ShortPixel::getClient()->download($optURL, $target, $optSize);
                    if($downloadOK <= 0) { // the size is wrong - probably in metadata, retry the image altogether
                        $found = $this->findItem($item, $pending, "OriginalURL");
                        if($found === false) {
                            $item->Status->Code = 1;
                            $item->Status->Message = "Pending";
                            $item->Retries = 1;
                            $pending[] = $item;
                            $this->persist($item, $cmds, 'pending');
                        } elseif($pending[$found]->Retries <= 3) {
                            $pending[$found]->Retries += 1;
                        } else {
                            $item->Status->Code = -1;
                            $item->Status->Message = "Wrong size, expected $optSize downloaded " . (-$downloadOK);
                            $failed[] = $item;
                            $this->commander->isDone($item);
                            $this->removeItem($item, $pending, "OriginalURL");
                        }
                        $this->logger->log(SPLog::PRODUCER_RESULT, "DOWNLOAD NOK? " . $downloadOK . " URL: " . $optURL, $item->Status->Message);
                        continue;
                    }
                    $this->checkSaveWebP($item, $target, $cmds);
                    $this->checkSaveAVIF($item, $target, $cmds);
                }
                catch(ClientException $e) {
                    $failed[] = $item;
                    $item->Status->Message = $e->getMessage();
                    $this->persist($item, $cmds, 'error');
                    $this->logger->log(SPLog::PRODUCER_RESULT, "CLIENT EXCEPTION:", $e->getMessage());
                    continue;
                }

                $succeeded[] = $item;
                $this->persist($item, $cmds);

                //remove from pending
                $this->removeItem($item, $pending, "OriginalURL"); //TODO check if fromURL and if not, use file path
                //tell the commander that the item is done so it won't be relaunched
                $this->commander->isDone($item);
                $i++;
            }

            //For the pending items relaunch, or if any item that needs to be retried from file (-102 or -106)
            if($retry || count($pending)) {
                if(!$item->OriginalURL) foreach($pending as $pend) {
                    if(!isset($this->ctx->fileMappings[$pend->OriginalURL])) {
                        $pend = false;
                    }
                }
                $this->logger->log(SPLog::PRODUCER_RESULT, "RELAUNCH");
                $this->ctx = $this->commander->relaunch((object)array("body" => $pending, "headers" => $this->ctx->headers, "fileMappings" => $this->ctx->fileMappings));
            } else {
                break;
            }
            if($this->ctx == false) { //time's up
                break;
            }
        }

        $message = ShortPixel::opt('persist_type') == 'text' ? 'pending' : ($pending ? 'pending' : ($failed ? 'error' : 'success'));
        $ret = (object) array(
            'status' => array(
                'code' => ($message == 'success' ? 2 : 1),
                'message' => $message),
            'succeeded' => $succeeded,
            'pending' => $pending,
            'failed' => $failed,
            'same' => $same
        );
        if($cmds['notify_progress']) {
            //notify the progress to whom it may concern :)
            $notifier = ProgressNotifier::constructNotifier($path);
            $notifier->recordProgress($ret);
        }

        return $ret;
    }

    private function checkSaveWebP($item, $target, $cmds)
    {
        if (isset($item->WebPLossyURL) && $item->WebPLossyURL !== 'NA') { //a WebP image was generated as per the options, download and save it too
            $webpTarget = $targetWebPFile = dirname($target) . '/' . MB_basename($target, '.' . pathinfo($target, PATHINFO_EXTENSION)) . ".webp";
            $optWebPURL = $cmds["lossy"] > 0 ? $item->WebPLossyURL : $item->WebPLosslessURL;
            ShortPixel::getClient()->download($optWebPURL, $webpTarget);
            $item->WebPSavedFile = $webpTarget;
        }
    }

    private function checkSaveAVIF($item, $target, $cmds)
    {
        if (isset($item->AVIFLossyURL) && $item->AVIFLossyURL !== 'NA') { //an AVIF image was generated as per the options, download and save it too
            $avifTarget = $targetAVIFFile = dirname($target) . '/' . MB_basename($target, '.' . pathinfo($target, PATHINFO_EXTENSION)) . ".avif";
            $optAVIFURL = $cmds["lossy"] > 0 ? $item->AVIFLossyURL : $item->AVIFLosslessURL;
            ShortPixel::getClient()->download($optAVIFURL, $avifTarget);
            $item->AVIFSavedFile = $avifTarget;
        }
    }

    private function persist($item, $cmds, $status = 'success') {
        $pers = ShortPixel::getPersister();
        if($pers) {
            $optParams = $this->optimizationParams($item, $cmds);
            if($status == 'pending') {
                $optParams['message'] = $item->OriginalURL;
                return $pers->setPending($item->SavedFile, $optParams);
            } elseif ($status == 'error') {
                $optParams['message'] = $item->Status->Message;
                return $pers->setFailed($item->SavedFile, $optParams);
            } elseif ($status == 'skip') {
                $optParams['message'] = $item->Status->Message;
                return $pers->setSkipped($item->SavedFile, $optParams, 'skip');
            } else {
                return $pers->setOptimized($item->SavedFile, $optParams);
            }
        }
    }

    private function optimizationParams($item, $cmds) {
        $optimizedSize = $item->Status->Code == 2 ? ($cmds["lossy"] > 0 ? $item->LossySize : $item->LoselessSize) : 0;

        return array(
            "compressionType" => $cmds["lossy"] == 1 ? 'lossy' : ($cmds["lossy"] == 2 ? 'glossy' : 'lossless'),
            "keepExif" => isset($cmds['keep_exif']) ? $cmds['keep_exif'] : ShortPixel::opt("keep_exif"),
            "cmyk2rgb" => isset($cmds['cmyk2rgb']) ? $cmds['cmyk2rgb'] : ShortPixel::opt("cmyk2rgb"),
            "resize" => isset($cmds['resize'])
                        ? $cmds['resize']
                        : ((isset($cmds['resize_width']) && $cmds['resize_width'] > 0)
                           ? 1 : (ShortPixel::opt("resize_width") ? 1 : 0)),
            "resizeWidth" => isset($cmds['resize_width']) ? $cmds['resize_width'] : ShortPixel::opt("resize_width"),
            "resizeHeight" => isset($cmds['resize_height']) ? $cmds['resize_height'] : ShortPixel::opt("resize_height"),
            "percent" => isset($item->PercentImprovement) ? number_format(100.0 - 100.0 * $optimizedSize / $item->OriginalSize, 2) : 0, //$item->PercentImprovement : 0,
            "optimizedSize" => $optimizedSize,
            "changeDate" => time(),
            "message" => null //will be set by persist() if 'error'
        );
    }

    /**
     * finds if an array contains an item, comparing the property given as key
     * @param $item
     * @param $arr
     * @param $key
     * @return the position that was removed, false if not found
     */
    private function findItem($item, $arr, $key) {
        for($j = 0; $j < count($arr); $j++) {
            if($arr[$j]->$key == $item->$key) {
                return $j;
            }
        }
        return false;
    }

    /**
     * removes the item if found in array (with findItem)
     * @param $item
     * @param $arr
     * @param $key
     * @return true if removed, false if not found
     */
    private function removeItem($item, &$arr, $key) {
        $j = $this->findItem($item, $arr, $key);
        if($j !== false) {
            array_splice($arr, $j);
            return true;
        }
        return false;
    }
}
