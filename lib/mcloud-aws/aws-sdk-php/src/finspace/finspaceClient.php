<?php

namespace MediaCloud\Vendor\Aws\finspace;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * This client is used to interact with the **FinSpace User Environment Management service** service.
 * @method \MediaCloud\Vendor\Aws\Result createEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnvironmentAsync(array $args = [])
 */
class finspaceClient extends AwsClient {}
