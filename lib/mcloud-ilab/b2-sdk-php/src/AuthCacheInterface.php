<?php

namespace MediaCloud\Vendor\ILAB\B2;

interface AuthCacheInterface {
    /**
     * Returns the auth data for the given key
     *
     * @param $key
     * @return array|null
     */
    public function cachedB2Auth($key);

    /**
     * Caches authentication data
     * @param $key
     * @param $authData
     */
    public function cacheB2Auth($key, $authData);
}