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

    private function __construct() {
        $this->time = \MediaCloud\Vendor\ShortPixel\opt("cache_time");
        $this->mc = \MediaCloud\Vendor\ShortPixel\getMemcache();
        if(!$this->mc) {
            $this->local = array();
        }
    }

    public function fetch($key) {
        if($this->mc) {
            return $this->mc->get($key);
        } elseif(isset($this->local[$key]) && time() - $this->local[$key]['time'] < $this->time) {
            return $this->local[$key]['value'];
        }
        return false;
    }

    public function store($key, $value) {
        if($this->time) {
            if($this->mc) {
                return $this->mc->set($key, $value, $this->time);
            } else {
                $this->local[$key] = array('value' => $value, 'time' => time());
            }
        }
        return false;
    }

    public function delete($key) {
        if($this->mc) {
            return $this->mc->delete($key);
        } elseif(isset($this->local[$key])) {
            unset($this->local[$key]);
        }
        return false;
    }

    /**
     * returns the current logger. If the logger is not set to log from that producer or if the log is not initialized, will return a dummy logger which doesn't log.
     * @return SPCache
     */
    public static function Get() {
        if(!isset(self::$instance)) {
            self::$instance = new SPCache();
        }
        return self::$instance;
    }

}