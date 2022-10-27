<?php

namespace MediaCloud\Vendor\Aws\Synthetics;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Synthetics** service.
 * @method \MediaCloud\Vendor\Aws\Result createCanary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCanaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCanary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCanaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCanaries(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCanariesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCanariesLastRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCanariesLastRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRuntimeVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRuntimeVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCanary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCanaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCanaryRuns(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCanaryRunsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startCanary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startCanaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopCanary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopCanaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateCanary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCanaryAsync(array $args = [])
 */
class SyntheticsClient extends AwsClient {}
