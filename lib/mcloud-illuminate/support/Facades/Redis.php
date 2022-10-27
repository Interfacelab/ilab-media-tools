<?php

namespace MediaCloud\Vendor\Illuminate\Support\Facades;

/**
 * @method static \Illuminate\Redis\Connections\Connection connection(string $name = null)
 * @method static \Illuminate\Redis\Limiters\ConcurrencyLimiterBuilder funnel(string $name)
 * @method static \Illuminate\Redis\Limiters\DurationLimiterBuilder throttle(string $name)
 *
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
