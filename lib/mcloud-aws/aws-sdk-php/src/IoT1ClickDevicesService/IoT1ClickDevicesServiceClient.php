<?php

namespace MediaCloud\Vendor\Aws\IoT1ClickDevicesService;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT 1-Click Devices Service** service.
 * @method \MediaCloud\Vendor\Aws\Result claimDevicesByClaimCode(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise claimDevicesByClaimCodeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDevice(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDeviceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result finalizeDeviceClaim(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise finalizeDeviceClaimAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDeviceMethods(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDeviceMethodsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result initiateDeviceClaim(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise initiateDeviceClaimAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result invokeDeviceMethod(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise invokeDeviceMethodAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDeviceEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDeviceEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDevices(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDevicesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unclaimDevice(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unclaimDeviceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDeviceState(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDeviceStateAsync(array $args = [])
 */
class IoT1ClickDevicesServiceClient extends AwsClient {}
