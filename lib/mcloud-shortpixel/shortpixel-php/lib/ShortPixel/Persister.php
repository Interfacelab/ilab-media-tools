<?php
/**
 * User: simon
 * Date: 19.08.2016
 * Time: 18:01
 */

namespace MediaCloud\Vendor\ShortPixel;

interface Persister {

    static function IGNORED_BY_DEFAULT();
    function __construct($options);
    function isOptimized($path);
    function getOptimizationData($path);
    function info($path, $recurse = true, $fileList = false, $exclude = array(), $persistPath = false);
    function getTodo($path, $count, $exclude = array(), $persistFolder = false, $maxTotalFileSize = false, $recurseDepth = PHP_INT_MAX);
    function getNextTodo($path, $count);
    function doneGet();
    function setPending($path, $optData);
    function setOptimized($path, $optData);
    function setFailed($path, $optData);
    function setSkipped($path, $optData);
}