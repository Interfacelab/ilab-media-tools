<?php
/**
 * User: simon
 * Date: 16.03.2018
 */

namespace MediaCloud\Vendor\ShortPixel\notify;

class ProgressNotifierMemcache extends ProgressNotifier {
    protected $mc;
    protected $key;

    public function __construct($path) {
        parent::__construct($path);
        $this->key = md5($path);
    }

    public function setMemcache($mc) {
        $this->mc = $mc;
    }

    public function set($type, $val)
    {
        $data = $this->mc->get($this->key);
        $data[$type] = $val;
        $this->mc->set($this->key, $data);
    }

    public function get($type)
    {
        $data = $this->mc->get($this->key);
        return isset($data[$type]) ? $data[$type] : false;
    }

    public function getData()
    {
        return $this->mc->get($this->key);
    }

    public function setData($data)
    {
        $this->mc->set($this->key, $data);
    }
}