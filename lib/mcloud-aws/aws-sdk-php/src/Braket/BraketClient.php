<?php

namespace MediaCloud\Vendor\Aws\Braket;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Braket** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelQuantumTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelQuantumTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createQuantumTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createQuantumTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDevice(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDeviceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getQuantumTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getQuantumTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result searchDevices(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise searchDevicesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result searchQuantumTasks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise searchQuantumTasksAsync(array $args = [])
 */
class BraketClient extends AwsClient {}
