<?php
/**
 * User: simon
 * Date: 16.03.2018
 */

namespace MediaCloud\Vendor\ShortPixel\notify;

class ProgressNotifierFileQ extends ProgressNotifier {

    public function get($type)
    {
        $data = $this->getData();
        return isset($data[$type]) ? $data[$type] : false;
    }

    public function set($type, $value)
    {
        $data = $this->getData();
        if(!is_array($data)) {
            $data = [];
        }
        $data[$type] = $value;
        $this->setData($data);
    }

    const PROGRESS_FILE_NAME = '.sp-progress';

    public function getFilePath() {
        return rtrim( $this->path, '/\\' ) . '/' . self::PROGRESS_FILE_NAME;
    }

    public function getData() {
        $file = $this->getFilePath();
        if(file_exists($file)) {
            return json_decode(file_get_contents($file));
        }
        return false;
    }

    function setData($data) {
        file_put_contents($this->getFilePath(), json_encode($data));
    }
}