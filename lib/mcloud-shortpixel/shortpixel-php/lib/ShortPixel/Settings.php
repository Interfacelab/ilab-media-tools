<?php

namespace MediaCloud\Vendor\ShortPixel;


class Settings {
    const FOLDER_INI_NAME = '.sp-options';
    private $INI_PATH;
    private $settings;

    function __construct($iniPath = false) {
        $this->INI_PATH = $iniPath;
        $this->settings = array();
        if(file_exists($this->INI_PATH)) {
            $this->settings = parse_ini_file($this->INI_PATH);
        }
    }

    function get($key) {
        return(isset($this->settings[$key]) ? $this->settings[$key] : false);
    }

    function persistApiKeyAndSettings($data) {
        if(isset($data['API_KEY']) && strlen($data['API_KEY']) == 20) {
            if(file_exists($this->INI_PATH)) {
                unlink($this->INI_PATH);
            }
            $strSettings = "[SHORTPIXEL]\nAPI_KEY=" . $data['API_KEY'] . "\n";
            $settings = $this->post2options($data);
            foreach($settings as $key => $val) {
                $strSettings .= $key . '=' . $val . "\n";
            }
            $settings['API_KEY'] = $data['API_KEY'];

            if(!@file_put_contents($this->INI_PATH, $strSettings)) {
                return array("error" => "Could not write properties file " . $this->INI_PATH . ". Please check rights.");
            }
            $this->settings = $settings;
            return array("success" => "API Key set: " . $data['API_KEY']);
        } else {
            return array("error" => "API Key should be 20 characters long.");
        }
    }

    function post2options($post) {
        $data = array();
        if(isset($post['type'])) $data['lossy'] = $post['type'] == 'lossy' ? 1 : ($post['type'] == 'glossy' ? 2 : 0);
        $data['keep_exif'] = isset($post['removeExif']) ? 0 : 1;
        $data['cmyk2rgb'] = isset($post['cmyk2rgb']) ? 1 : 0;
        $data['resize'] = isset($post['resize']) ? ($post['resize_type'] == 'outer' ? 1 : 3) : 0;
        if($data['resize'] && isset($post['width'])) $data['resize_width'] = $post['width'];
        if($data['resize'] && isset($post['height'])) $data['resize_height'] = $post['height'];

        $convertto = isset($post['webp']) ? '|+webp' : '';
        $convertto .= isset($post['avif']) ? '|+avif' : '';
        $data['convertto'] = '' . substr($convertto, 1);

        if(isset($post['backup_path'])) {
            $data['backup_path'] = $post['backup_path'];
        }
        if(isset($post['exclude'])) {
            $data['exclude'] = $post['exclude'];
        }
        if(isset($post['user']) && isset($post['pass'])) {
            $data['user'] = $post['user'];
            $data['pass'] = $post['pass'];
        }
        if(isset($post['base_url']) && strlen($post['base_url'])) {
            $data['base_url'] = rtrim($post['base_url'], '/');
        } elseif (isset($post['change_base_url']) && strlen($post['change_base_url'])) {
            $data['base_url'] = rtrim($post['change_base_url'], '/');
        }
        return $data;
    }

    static function pathToRelative($path, $reference) {
        $pa = explode('/', trim($path, '/'));
        $ra = explode('/', trim($reference, '/'));
        $res = array();
        for($i = 0, $same = true; $i < max(count($pa), count($ra)); $i++) {
            if($same && isset($pa[$i]) && isset($ra[$i]) && $pa[$i] == $ra[$i]) continue;
            $same = false;
            if(isset($ra[$i])) array_unshift($res, '..');
            if(isset($pa[$i])) $res[] = $pa[$i];
        }
        return implode('/', $res);
    }

    function persistFolderSettings($data, $path) {
        $strSettings = "[SHORTPIXEL]\n";
        foreach($this->post2options($data) as $key => $val) {
            if(!in_array($key, array("API_KEY", "folder", "")))
                $strSettings .= $key . '=' . (is_numeric($val) ? $val : '"' . $val . '"') . "\n";
        }
        return @file_put_contents($path . '/' . self::FOLDER_INI_NAME, $strSettings);
    }

    function addOptions($options) {
        array_merge($this->settings, $options);
    }

    function readOptions($path) {
        $options = $this->settings;
        if($path && file_exists($path . '/' . self::FOLDER_INI_NAME)) {
            $options = array_merge($options, parse_ini_file($path . '/' . self::FOLDER_INI_NAME));
        }
        unset($options['API_KEY']);
        return $options;

    }
}
