<?php

namespace MediaCloud\Vendor\Illuminate\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \MediaCloud\Vendor\Illuminate\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
