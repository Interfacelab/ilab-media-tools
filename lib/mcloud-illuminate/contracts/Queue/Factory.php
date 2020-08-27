<?php

namespace MediaCloud\Vendor\Illuminate\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string  $name
     * @return \MediaCloud\Vendor\Illuminate\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
