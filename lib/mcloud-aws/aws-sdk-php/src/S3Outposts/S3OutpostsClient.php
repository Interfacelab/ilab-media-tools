<?php

namespace MediaCloud\Vendor\Aws\S3Outposts;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon S3 on Outposts** service.
 * @method \MediaCloud\Vendor\Aws\Result createEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEndpoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEndpointsAsync(array $args = [])
 */
class S3OutpostsClient extends AwsClient {}
