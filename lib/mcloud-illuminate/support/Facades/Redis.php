<?php

namespace MediaCloud\Vendor\Illuminate\Support\Facades;

/**
 * @see \Illuminate\Redis\RedisManager
 * @see \MediaCloud\Vendor\Illuminate\Contracts\Redis\Factory
 */
class Redis extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'redis';
    }
}
