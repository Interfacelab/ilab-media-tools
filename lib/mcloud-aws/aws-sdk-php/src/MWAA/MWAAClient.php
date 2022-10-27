<?php

namespace MediaCloud\Vendor\Aws\MWAA;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AmazonMWAA** service.
 * @method \MediaCloud\Vendor\Aws\Result createCliToken(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCliTokenAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createWebLoginToken(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createWebLoginTokenAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result publishMetrics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishMetricsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnvironmentAsync(array $args = [])
 */
class MWAAClient extends AwsClient {}
