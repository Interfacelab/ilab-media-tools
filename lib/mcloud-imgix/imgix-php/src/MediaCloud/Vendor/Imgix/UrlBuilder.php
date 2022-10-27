<?php

namespace MediaCloud\Vendor\Imgix;
use MediaCloud\Vendor\Imgix\Validator;

class UrlBuilder {

    private $currentVersion = "3.3.1";
    private $domain;
    private $useHttps;
    private $signKey;

    const TARGET_WIDTHS = array(
        100, 116, 134, 156, 182, 210, 244, 282,
        328, 380, 442, 512, 594, 688, 798, 926,
        1074, 1246, 1446, 1678, 1946, 2258, 2618,
        3038, 3524, 4088, 4742, 5500, 6380, 7400, 8192);

    // define class constants
    // should be private; but visibility modifiers are not supported php version <7.1
    const TARGET_RATIOS = array(1, 2, 3, 4, 5);

    const DPR_QUALITIES = array(1 => 75, 2 => 50, 3 => 35, 4 => 23, 5 => 20);

    const MIN_WIDTH = 100;
    const MAX_WIDTH = 8192;
    const SRCSET_WIDTH_TOLERANCE = 0.08;

    // constants cannot be dynamically assigned; keeping as a class variable instead
    private $targetWidths;

    public function __construct($domain, $useHttps = true, $signKey = "", $includeLibraryParam = true) {

        if (!is_string($domain)) {
            throw new \InvalidArgumentException("UrlBuilder must be passed a string domain");
        }

        $this->domain = $domain;
        $this->validateDomain($this->domain);        

        $this->useHttps = $useHttps;
        $this->signKey = $signKey;
        $this->includeLibraryParam = $includeLibraryParam;
        $this->targetWidths = $this->targetWidths();
    }

    private function validateDomain($domain) {
        $DOMAIN_PATTERN = "/^(?:[a-z\d\-_]{1,62}\.){0,125}(?:[a-z\d](?:\-(?=\-*[a-z\d])|[a-z]|\d){0,62}\.)[a-z\d]{1,63}$/";

        if(!preg_match($DOMAIN_PATTERN, $domain)) {
            throw new \InvalidArgumentException('Domain must be passed in as fully-qualified ' . 
            'domain name and should not include a protocol or any path element, i.e. ' .
            '"example.imgix.net".'); 
        }
    }

    public function setSignKey($key) {
        $this->signKey = $key;
    }

    public function setUseHttps($useHttps) {
        $this->useHttps = $useHttps;
    }

    public function setIncludeLibraryParam($includeLibraryParam) {
        $this->includeLibraryParam = $includeLibraryParam;
    }

    public function createURL($path, $params=array()) {
        $scheme = $this->useHttps ? "https" : "http";
        $domain = $this->domain;

        if ($this->includeLibraryParam) {
            $params['ixlib'] = "php-" . $this->currentVersion;
        }

        $uh = new UrlHelper($domain, $path, $scheme, $this->signKey, $params);

        return $uh->getURL();
    }

    public function createSrcSet($path, $params=array(), $options=array()) {
        $widthsArray = isset($options['widths']) ? $options['widths'] : NULL;

        if (isset($widthsArray)) {
            Validator::validateWidths($widthsArray);
            return $this->createSrcSetPairs($path, $params=$params, $widthsArray);
        }
        
        $_start = isset($options['start']) ? $options['start'] : self::MIN_WIDTH;
        $_stop = isset($options['stop']) ? $options['stop'] : self::MAX_WIDTH;
        $_tol = isset($options['tol']) ? $options['tol'] : self::SRCSET_WIDTH_TOLERANCE;
        $_disableVariableQuality = isset($options['disableVariableQuality'])
                ? $options['disableVariableQuality'] : false;

        if ($this->isDpr($params)) {
            return $this->createDPRSrcSet(
                $path=$path, 
                $params=$params, 
                $disableVariableQuality=$_disableVariableQuality);
        }
        else {
            $targets = $this->targetWidths($start=$_start, $stop=$_stop, $tol=$_tol);
            return $this->createSrcSetPairs($path, $params=$params, $targets=$targets);
        }
    }

    /**
     * Generate a list of target widths.
     * 
     * This function generates an array of target widths used to
     * width-describe image candidate strings (URLs) within a
     * srcset attribute.
     * 
     * This function returns an array of integer values that denote
     * image target widths. This array begins with `$start`
     * and ends with `$stop`. The `$tol` or tolerance value dictates
     * the amount of tolerable width variation between each width
     * in the range of values that lie between `$start` and `$stop`.
     * 
     * @param  int $start Starting minimum width value.
     * @param  int $stop Stopping maximum width value.
     * @param  int $tol Tolerable amount of width variation.
     * @return int[] $resolutions An array of integer values.
     */
    public function targetWidths(
        $start=self::MIN_WIDTH,
        $stop=self::MAX_WIDTH,
        $tol=self::SRCSET_WIDTH_TOLERANCE) {

        if ($start == $stop) {
            return array((int) $start);
        }

        Validator::validateMinMaxTol($start, $stop, $tol);
        $resolutions = array();

        while ($start < $stop && $start < self::MAX_WIDTH) {
            array_push($resolutions, (int) round($start));
            $start *= 1 + $tol * 2;
        }

        // The most recently appended value may, or may not, be
        // the `stop` value. In order to be inclusive of the
        // stop value, check for this case and add it, if necessary.
        if (end($resolutions) < $stop) {
            array_push($resolutions, (int) $stop);
        }
        
        return $resolutions;
    }

    private function isDpr($params) {
        if (empty($params)) {
            // If the params array is empty, then
            // it is _not_ dpr based.
            return false;
        }

        $hasWidth = array_key_exists('w', $params) ? $params['w'] : NULL;
        $hasHeight = array_key_exists('h', $params) ? $params['h'] : NULL;
        $hasAspectRatio = array_key_exists('ar', $params) ? $params['ar'] : NULL;

        // If `params` have a width or height parameter then the
        // srcset to be constructed with these params _is dpr based
        return $hasWidth || $hasHeight;
    }

    private function createDPRSrcSet($path, $params, $disableVariableQuality=false) {
        $srcset = "";

        $size = count(self::TARGET_RATIOS);
        for ($i = 0; $i < $size; $i++) {
            $currentParams = $params;
            $currentParams['dpr'] = $i+1;
            $currentRatio = self::TARGET_RATIOS[$i];
            // If variable quality output has been disabled _and_
            // the `q` param _has not_ been passed:
            if (!$disableVariableQuality && !isset($params["q"])) {
                $currentParams["q"] = self::DPR_QUALITIES[$i + 1];
            }
            $srcset .= $this->createURL($path, $currentParams) . " " . $currentRatio . "x,\n";
        }

        return substr($srcset, 0, strlen($srcset) - 2);
    }

    private function createSrcSetPairs($path, $params, $targets=self::TARGET_WIDTHS) {
        $srcset = "";
        $currentWidth = NULL;
        $currentParams = NULL;

        $size = count($targets);
        for ($i = 0; $i < $size; $i++) {
            $currentWidth = $targets[$i];
            $currentParams = $params;
            $currentParams['w'] = $currentWidth;
            $srcset .= $this->createURL($path, $currentParams) . " " . $currentWidth . "w,\n";
        }

        return substr($srcset, 0, strlen($srcset) - 2);
    }
}
