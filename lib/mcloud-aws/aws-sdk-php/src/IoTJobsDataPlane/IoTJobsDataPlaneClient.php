<?php

namespace MediaCloud\Vendor\Aws\IoTJobsDataPlane;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Jobs Data Plane** service.
 * @method \MediaCloud\Vendor\Aws\Result describeJobExecution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJobExecutionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPendingJobExecutions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPendingJobExecutionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startNextPendingJobExecution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startNextPendingJobExecutionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateJobExecution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateJobExecutionAsync(array $args = [])
 */
class IoTJobsDataPlaneClient extends AwsClient {}
