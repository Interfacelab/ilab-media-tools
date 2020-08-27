<?php

namespace MediaCloud\Vendor\Aws\Exception;
use MediaCloud\Vendor\Aws\HasMonitoringEventsTrait;
use MediaCloud\Vendor\Aws\MonitoringEventsInterface;

class InvalidJsonException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
