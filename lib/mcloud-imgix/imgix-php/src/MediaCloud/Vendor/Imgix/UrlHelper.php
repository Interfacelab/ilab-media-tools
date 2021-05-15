<?php

namespace MediaCloud\Vendor\Imgix;

class UrlHelper {

    private $domain;
    private $path;
    private $scheme;
    private $signKey;
    private $params;

    public function __construct($domain, $path, $scheme = "http", $signKey = "", $params = array()) {
        $this->domain = $domain;
        $this->path = $this->formatPath($path);
        $this->scheme = $scheme;
        $this->signKey = $signKey;
        $this->params = $params;
    }

    public function formatPath($path) {
        if (!is_string($path) || strlen($path) < 1)
            return '/';

        // Strip leading slash first (we'll re-add after encoding)
        $path = preg_replace("/^\//", "", $path);

        if (preg_match("/^https?:\/\//", $path)) {
            // If this path is a full URL, encode the entire thing
            $path = rawurlencode($path);
        } else if (preg_match("/^https?:\/\/[^\s\/$.?#]*\.[^\s]*$/", rawurldecode($path))) {
            // Using @stephenhay's solution from https://mathiasbynens.be/demo/url-regex
            // to ensure URL's validity.
            // $path looks like a valid encoded URL, however, it may still have
            // unencoded unicode characters.
            $path = preg_replace_callback("/([^\w\-\/\:@%])/", function ($match) {
                return rawurlencode($match[0]);
            }, $path);
        } else {
            // If this path is just a path, only encode certain characters
            $path = preg_replace_callback("/([^\w\-\/\:@])/", function ($match) {
                return rawurlencode($match[0]);
            }, $path);
        }

        // Add a leading slash before returning
        return '/' . $path;
    }

    public function setParameter($key, $value) {
        if ($key && ($value || $value === 0)) {
            $this->params[$key] = $value;
        } else {
            if (array_key_exists($key, $this->params)) {
                unset($this->params[$key]);
            }
        }
    }

    public function deleteParameter($key) {
        unset($this->params[$key]);
    }

    public function getURL() {
        $queryPairs = array();

        if ($this->params) {
            ksort($this->params);

            foreach ($this->params as $key => $val) {
                if (substr($key, -2) == '64') {
                    $encodedVal = self::base64url_encode($val);
                } else {
                    $encodedVal = is_array($val) ? rawurlencode(implode(',',$val)) : rawurlencode($val);
                }

                $queryPairs[] = rawurlencode($key) . "=" . $encodedVal;
            }
        }

        $query = join("&", $queryPairs);
        if ($query) {
            $query = '?' . $query;
        }

        if ($this->signKey) {
            $toSign = $this->signKey . $this->path . $query;
            $sig = md5($toSign);
            if ($query) {
                $query .= "&s=" . $sig;
            } else {
                $query = "?s=" . $sig;
            }
        }

        $url_parts = array('scheme' => $this->scheme, 'host' => $this->domain, 'path' => $this->path, 'query' => $query);

        return self::joinURL($url_parts);
    }

    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function joinURL($parts) {
        $url = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . $parts['query'];

        return $url;
    }
}
