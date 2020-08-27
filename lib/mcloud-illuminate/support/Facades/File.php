<?php

namespace MediaCloud\Vendor\Illuminate\Support\Facades;

/**
 * @see \MediaCloud\Vendor\Illuminate\Filesystem\Filesystem
 */
class File extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'files';
    }
}
