<?php
/**
 * User: simon
 * Date: 21.12.2017
 * Time: 23:41
 */

namespace MediaCloud\Vendor\ShortPixel;

class Lock {
    const FOLDER_LOCK_FILE = '.sp-lock';

    private $processId, $targetFolder, $clearLock, $releaseTo, $timeout;

    /**
     * @param $processId
     * @param $targetFolder
     * @param bool|false $clearLock
     * @param string|false $releaseTo a string that if found in the lock file, will make lock() release the lock instead of updating it
     * - use together with requestLock to pass the lock between concurent processes with different priority (call requestLock with the same value as $requester on the script that should take the lock).
     */
    function __construct($processId, $targetFolder, $clearLock = false, $releaseTo = false, $timeout = 360) {
        $this->processId = $processId;
        $this->targetFolder = $targetFolder;
        $this->clearLock = $clearLock;
        $this->releaseTo = $releaseTo;
        $this->timeout = $timeout;
        $this->logger = SPLog::Get(SPLog::PRODUCER_PERSISTER);
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function lockFile() {
        return $this->targetFolder . '/' . self::FOLDER_LOCK_FILE;
    }

    function readLock() {
        if(file_exists($this->lockFile())) {
            $lock = file_get_contents($this->targetFolder . '/' . self::FOLDER_LOCK_FILE);
            return explode("=", $lock);
        }
        return false;
    }

    function lock() {
    //check if the folder is not locked by another ShortPixel process
        if(!$this->clearLock && ($lock = $this->readLock()) !== false) {
            $time = explode('!', $lock[1]);
            if(count($lock) >= 2 && $lock[0] != $this->processId && $time[0] > time() - (isset($time[1]) ? $time[1] : $this->timeout)) {
                //a lock was placed on the file and it's not yet expired as per its set timeout
                throw new \Exception($this->getLockMsg($lock, $this->targetFolder), -19);
            }
            elseif(count($lock) >= 4 && $lock[2] == $this->releaseTo) {
                // a request to release the lock was received
                unlink($this->lockFile());
                throw new \Exception("A lock release was requested by " . $this->releaseTo, -20);
            }
        }
        if(FALSE === @file_put_contents($this->lockFile(), $this->processId . "=" . time() . '!' . $this->timeout . (strlen($this->releaseTo) ? "=" . $this->releaseTo : ''))) {
            throw new ClientException("Could not write lock file " . $this->lockFile() . ". Please check rights.", -16);
        }
        $this->logger->log(SPLog::PRODUCER_PERSISTER, "{$this->processId} locked " . dirname($this->lockFile()) . " for {$this->timeout} sec.");
    }

    function requestLock($requester) {
        if(($lock = $this->readLock()) !== false) {
            if(isset($lock[2]) && $lock[2] == $requester) {
                //the script that locked the folder will accept a request from $requester to give the lock
                //mark in the lock a request to release it
                if(FALSE === @file_put_contents($this->lockFile(), $lock[0] . "=" . $lock[1] . "=" . $requester . "=true")) {
                    throw new ClientException("Could not update lock file " . $this->lockFile() . ". Please check rights.", -16);
                }
            } else {
                //the script will not accept a request from $requester, maybe the lock is old?
                $this->lock();
                return;
            }
        }
        //now wait for the other process to release the lock, a bit more than its expiry time - in case it was left there...
        $expiry = max(1, 365 - (time() - $lock[1]));
        for($i = 0; $i < $expiry; $i++) {
            if(file_exists($this->lockFile())) {
                sleep(1);
            } else {
                break;
            }
        }
        $this->lock();
    }

    function unlock() {
        if(($lock = $this->readLock()) !== false) {
            if($lock[0] == $this->processId) {
                unlink($this->lockFile());
            }
        }
    }

    function getLockMsg($lock, $folder) {
        return SPLog::format("The folder is locked by a different ShortPixel process ({$lock[0]}). Exiting. \n\n\033[31mIf you're SURE no other ShortPixel process is running, you can remove the lock with \n\n >\033[34m rm " . $folder . '/' . self::FOLDER_LOCK_FILE . " \033[0m \n");
    }
}