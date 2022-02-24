<?php

namespace MediaCloud\Vendor\Aws\IoTFleetHub;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Fleet Hub** service.
 * @method \MediaCloud\Vendor\Aws\Result createApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listApplications(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 */
class IoTFleetHubClient extends AwsClient {}
