<?php

namespace MediaCloud\Vendor\Aws\CloudWatchRUM;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **CloudWatch RUM** service.
 * @method \MediaCloud\Vendor\Aws\Result createAppMonitor(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAppMonitorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAppMonitor(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAppMonitorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAppMonitor(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAppMonitorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAppMonitorData(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAppMonitorDataAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAppMonitors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAppMonitorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putRumEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putRumEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateAppMonitor(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateAppMonitorAsync(array $args = [])
 */
class CloudWatchRUMClient extends AwsClient {}
