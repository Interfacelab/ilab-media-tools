<?php
/**
 * User: simon
 * Date: 26.02.2018
 */

namespace MediaCloud\Vendor\ShortPixel;

/**
 * Class SPLog logs messages or not based on which source (producer)
 * @package ShortPixel
 */
class SPLog {
    const PRODUCER_NONE = 0;
    const PRODUCER_CMD = 1;         //0b00000001;
    const PRODUCER_CMD_VERBOSE = 2; //0b00000010;
    const PRODUCER_PERSISTER = 4;   //0b00000100;
    const PRODUCER_CLIENT = 8;      //0b00001000;
    const PRODUCER_RESULT = 16;     //0b00010000;
    const PRODUCER_WEB = 32;        //0b00100000;
    const PRODUCER_CTRL = 64;       //0b01000000;
    const PRODUCER_SOURCE = 128;    //0b10000000;
    const PRODUCER_CACHE = 256;    //0b100000000;

    const FLAG_NONE = 0;
    const FLAG_MEMORY = 1;

    const TARGET_CONSOLE = 1;
    const TARGET_FILE = 2;

    private static $instance, $dummy;

    private $processId;
    private $target;
    private $targetName;
    private $acceptedProducers;
    private $time;
    private $loggedAlready;
    private $flags;

    private function __construct($processId, $acceptedProducers, $target, $targetName, $flags = self::FLAG_NONE) {
        $this->processId = $processId;
        $this->acceptedProducers = $acceptedProducers;
        $this->target = $target;
        $this->targetName = $targetName;
        $this->time = microtime(true);
        $this->loggedAlready = array();
        $this->flags = $flags;
    }

    /**
     * formats a log message
     * @param $processId
     * @param $msg
     * @param $time
     * @return string
     */
    public static function format($msg, $processId = false, $time = false, $flags = self::FLAG_NONE) {
        return "\n" . ($processId ? "$processId@" : "")
                    . date("Y-m-d H:i:s")
                    . ($time ? " (" . number_format(microtime(true) - $time, 2) . "s)" : "")
                    . ($flags | self::FLAG_MEMORY ? " (M: " . number_format(memory_get_usage()) . ")" : ""). " > $msg\n";
    }

    /**
     * Log the message if the logger is configured to log from this producer
     * @param $producer SPLog::PRODUCER_* - the source of logging ( one of the SPLog::PRODUCER_* values )
     * @param $msg $string the actual message
     * @param bool $object
     */
    public function log($producer, $msg, $object = false) {
        if(!($this->acceptedProducers & $producer)) { return; }

        $msgFmt = self::format($msg, $this->processId, $this->time, $this->flags);
        if($object) {
            $msgFmt .= " " . json_encode($object);
        }
        $this->logRaw($msgFmt);
    }

    /**
     * Log only the first call with that key
     * @param $key
     * @param $producer
     * @param $msg
     * @param $object
     */
    public function logFirst($key, $producer, $msg, $object = false) {
        if(!($this->acceptedProducers & $producer)) { return; }

        if(!in_array($key, $this->loggedAlready)) {
            $this->loggedAlready[] = $key;
            $this->log($producer, $msg, $object);
        }

    }

    public function clearLogged($producer, $key) {
        if(!($this->acceptedProducers & $producer)) { return; }

        if (($idx = array_search($key, $this->loggedAlready)) !== false) {
            unset($this->loggedAlready[$idx]);
        }
    }

    /**
     * logs a message regardless of the producer setting and without formatting
     * @param $msg
     */
    public function logRaw($msg){
        switch($this->target) {
            case self::TARGET_CONSOLE:
                echo($msg);
                break;
            case self::TARGET_FILE:
                $ret = file_put_contents($this->targetName, $msg, FILE_APPEND);
                break;
                
        }
    }

    /**
     * Log the message if the logger is configured to log from this producer AND EXIT ANYWAY
     * @param $producer the source of logging ( one of the SPLog::PRODUCER_* values )
     * @param $msg the actual message
     * @param bool $object
     */
    public function bye($producer, $msg, $object = false) {
        $this->log($producer, $msg, $object); echo("\n");die();
    }

    /**
     * init the logger singleton
     * @param $processId
     * @param $acceptedProducers - the producers from which the logger will log, ignoring gracefully the others
     * @param int $target - the log type
     * @param bool|false $targetName the log name if needed
     * @return SPLog the newly created logger instance
     */
    public static function Init($processId, $acceptedProducers, $target = self::TARGET_CONSOLE, $targetName = false, $flags = SPLog::FLAG_NONE) {
        self::$instance = new SPLog($processId, $acceptedProducers, $target, $targetName, $flags);
        return self::$instance;
    }

    /**
     * returns the current logger. If the logger is not set to log from that producer or if the log is not initialized, will return a dummy logger which doesn't log.
     * @param $producer
     * @return SPLog
     */
    public static function Get($producer) {
        if( !(self::$instance && ($producer & self::$instance->acceptedProducers)) ) {
            if(!isset(self::$dummy)) {
                self::$dummy = new SPLog(0, self::PRODUCER_NONE, self::TARGET_CONSOLE, false);
            }
            return self::$dummy;
        }
        return self::$instance;
    }

    /**
     * set the target - useful to change the target, for example in order to start logging in a file if a number of retries has been surpassed.
     * @param $target
     * @param false $targetName
     */
    public function setTarget($target, $targetName = false) {
        $this->target = $target;
        if($targetName) {
            $this->targetName = $targetName;
        }
    }
}