<?php

namespace MediaCloud\Vendor\Illuminate\Support\Facades;
use MediaCloud\Vendor\Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

/**
 * @see \MediaCloud\Vendor\Illuminate\Contracts\Routing\ResponseFactory
 */
class Response extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ResponseFactoryContract::class;
    }
}
