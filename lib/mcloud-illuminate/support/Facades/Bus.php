<?php

namespace MediaCloud\Vendor\Illuminate\Support\Facades;
use MediaCloud\Vendor\Illuminate\Support\Testing\Fakes\BusFake;
use MediaCloud\Vendor\Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;

/**
 * @see \MediaCloud\Vendor\Illuminate\Contracts\Bus\Dispatcher
 */
class Bus extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap(new BusFake);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BusDispatcherContract::class;
    }
}
