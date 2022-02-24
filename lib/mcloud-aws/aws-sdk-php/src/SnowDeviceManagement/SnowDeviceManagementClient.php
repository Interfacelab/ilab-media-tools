<?php

namespace MediaCloud\Vendor\Aws\SnowDeviceManagement;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Snow Device Management** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDevice(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDeviceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDeviceEc2Instances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDeviceEc2InstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeExecution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeExecutionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDeviceResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDeviceResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDevices(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDevicesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listExecutions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listExecutionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTasks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTasksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SnowDeviceManagementClient extends AwsClient {}
