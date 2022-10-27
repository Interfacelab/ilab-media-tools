<?php

namespace MediaCloud\Vendor\Cron;

interface FieldFactoryInterface
{
    public function getField(int $position): FieldInterface;
}
