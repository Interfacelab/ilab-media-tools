<?php

namespace MediaCloud\Vendor\Symfony\Component\Debug\Tests\Fixtures;
use MediaCloud\Vendor\Symfony\Component\Debug\BufferingLogger;

class LoggerThatSetAnErrorHandler extends BufferingLogger
{
    public function log($level, $message, array $context = [])
    {
        set_error_handler('is_string');
        parent::log($level, $message, $context);
        restore_error_handler();
    }
}
