<?php

namespace MediaCloud\Vendor\Aws\S3\UseArnRegion\Exception;
use MediaCloud\Vendor\Aws\HasMonitoringEventsTrait;
use MediaCloud\Vendor\Aws\MonitoringEventsInterface;

/**
 * Represents an error interacting with configuration for S3's UseArnRegion
 */
class ConfigurationException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
