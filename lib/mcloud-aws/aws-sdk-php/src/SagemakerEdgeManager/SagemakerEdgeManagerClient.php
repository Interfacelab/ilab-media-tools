<?php

namespace MediaCloud\Vendor\Aws\SagemakerEdgeManager;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Sagemaker Edge Manager** service.
 * @method \MediaCloud\Vendor\Aws\Result getDeviceRegistration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDeviceRegistrationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendHeartbeat(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendHeartbeatAsync(array $args = [])
 */
class SagemakerEdgeManagerClient extends AwsClient {}
