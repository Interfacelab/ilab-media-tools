<?php
/**
 * User: simon
 * Date: 27.07.2018
 */

namespace MediaCloud\Vendor\ShortPixel;

/**
 * Class SPCache will cache in memcached the objects received, if memcached is available
 * @package ShortPixel
 */
class SPCache {
    private static $instance;
    private $mc;
    private $local;
    private $time;
    private $logger;

    private function __construct() {
        $this->time = \MediaCloud\Vendor\ShortPixel\opt("cache_time");
        $this->logger = SPLog::Get(SPLog::PRODUCER_CACHE);
        $this->mc = \MediaCloud\Vendor\ShortPixel\getMemcache();
        $this->logger->log(SPLog::PRODUCER_CACHE, "Cache initialized, Expiry Time: " . $this->time . ' SEC.');
        if(!$this->mc) {
            $this->logger->log(SPLog::PRODUCER_CACHE, "Memcache not found, using local array.");
            $this->local = array();
        }
    }

    public function fetch($key) {
        $ret = false;
        if($this->mc) {
            $ret = $this->mc->get($key);
        } elseif(isset($this->local[$key]) && time() - $this->local[$key]['time'] < $this->time) {
            $ret = $this->local[$key]['value'];
        }
        $this->logger->log(SPLog::PRODUCER_CACHE, 'FETCHED KEY: ' . $key . ($ret ? ' VALUE: ' . print_r($ret, true) : ' UNFOUND'));
        return $ret;
    }

    public function store($key, $value) {
        if($this->time) {
            $this->logger->log(SPLog::PRODUCER_CACHE, 'STORING KEY: ' . $key . ' VALUE: ' . print_r($value, true));
            if($this->mc) {
                return $this->mc->set($key, $value, $this->time);
            } else {
                $this->local[$key] = array('value' => $value, 'time' => time());
            }
        }
        return false;
    }

    public function delete($key) {
        $this->logger->log(SPLog::PRODUCER_CACHE, 'DELETING KEY: ' . $key);
        if($this->mc) {
            return $this->mc->delete($key);
        } elseif(isset($this->local[$key])) {
            unset($this->local[$key]);
        }
        return false;
    }

    /**
     * returns the current cache provider.
     * @return SPCache
     */
    public static function Get() {
        if(!isset(self::$instance)) {
            self::$instance = new SPCache();
        }
        return self::$instance;
    }

}