<?php

namespace MediaCloud\Vendor\Aws\Braket;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Braket** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result cancelQuantumTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelQuantumTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createQuantumTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createQuantumTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDevice(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDeviceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getQuantumTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getQuantumTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result searchDevices(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise searchDevicesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result searchJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise searchJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result searchQuantumTasks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise searchQuantumTasksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class BraketClient extends AwsClient {}
