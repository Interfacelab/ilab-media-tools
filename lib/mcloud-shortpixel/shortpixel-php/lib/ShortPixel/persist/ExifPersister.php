<?php
/**
 * User: simon
 * Date: 19.08.2016
 * Time: 18:05
 */

namespace MediaCloud\Vendor\ShortPixel\persist;

use \MediaCloud\Vendor\ShortPixel\Persister;

class ExifPersister implements Persister {


    function __construct($options)
    {
        // nothing to do, the ExifPersister doesn't need any configuration
    }

    public static function IGNORED_BY_DEFAULT() {
        return array();
    }

    function isOptimized($path)
    {
        switch(exif_imagetype($path)) {
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
            case IMAGETYPE_TIFF_II:
            case IMAGETYPE_TIFF_MM:
                $exif = @exif_read_data($path, 0, true);
                if($exif===false) return false;
                foreach ($exif as $key => $section) {
                    if($key == "EXIF"){
                        foreach ($section as $name => $val) {
                            if($name === "UserComment") {
                                $code = substr($val, -5);
                                if($code === \MediaCloud\Vendor\ShortPixel\ShortPixel::LOSSLESS_EXIF_TAG || $code === \MediaCloud\Vendor\ShortPixel\ShortPixel::LOSSY_EXIF_TAG) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            break;
            case IMAGETYPE_PNG:
                $png = new PNGReader($path);
                $sections = $png->get_sections();
                $png = PNGMetadataExtractor::getMetadata($path);
                if(isset($png["text"])) {
                    if(is_array($png["text"])){
                        foreach($png["text"] as $key => $item) {
                            if($key == "APP1_Profile" && isset($item["x-default"])) {
                                $lines = explode("\n", $item["x-default"]);
                                if(isset($lines[7])){
                                    $val = trim(substr(hex2bin($lines[7]), -6));
                                    if($val === \MediaCloud\Vendor\ShortPixel\ShortPixel::LOSSLESS_EXIF_TAG || $val === \MediaCloud\Vendor\ShortPixel\ShortPixel::LOSSY_EXIF_TAG)
                                        return true;
                                }
                            }
                        }
                    } else {

                    }
                }
                /*if(isset($sections["COMMENT"]) && $sections["COMMENT"] == "SPXLL") {
                    return true;
                }*/
        }
        return false;
    }

    function info($path, $recurse = true, $fileList = false, $exclude = array(), $persistPath = false) {
        throw new Exception("Not implemented");
    }

    function getTodo($path, $count, $exclude = array(), $persistFolder = false, $maxTotalFileSize = false, $recurseDepth = PHP_INT_MAX)
    {
        $results = array();
        $this->getTodoRecursive($path, $count, array_values(array_merge($exclude, array('.','..'))), $results, $recurseDepth);
        return  $results;
    }

    private function getTodoRecursive($path, &$count, $ignore, &$results, $recurseDepth) {
        if($count <= 0) return;
        $files = scandir($path);
        foreach($files as $t) {
            if($count <= 0) return;
            if(in_array($t, $ignore)) continue;
            $tpath = rtrim($path, '/') . '/' . $t;
            if (is_dir($tpath)) {
                if($recurseDepth <= 0) continue;
                self::getTodoRecursive($tpath, $count, $ignore, $results, $recurseDepth -1);
            } elseif(\MediaCloud\Vendor\ShortPixel\ShortPixel::isProcessable($t)
                     && !$this->isOptimized($tpath)) {
                $results[] = $tpath;
                $count--;
            }
        }
    }

    function getOptimizationData($path)
    {
        // TODO: Implement getOptimizationData() method.
    }

    function getNextTodo($path, $count)
    {
        // TODO: Implement getNextTodo() method.
    }

    function doneGet()
    {
        // TODO: Implement doneGet() method.
    }

    function setPending($path, $optData)
    {
        // TODO: Implement setPending() method.
    }

    function setOptimized($path, $optData)
    {
        // TODO: Implement setOptimized() method.
    }

    function setFailed($path, $optData)
    {
        // TODO: Implement setFailed() method.
    }

    function setSkipped($path, $optData)
    {
        // TODO: Implement setSkipped() method.
    }
}