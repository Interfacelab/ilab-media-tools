<?php

namespace MediaCloud\Vendor\Aws\CodeStarconnections;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CodeStar connections** service.
 * @method \MediaCloud\Vendor\Aws\Result createConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHostAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHostAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHostAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listConnections(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listConnectionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHosts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHostsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateHost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateHostAsync(array $args = [])
 */
class CodeStarconnectionsClient extends AwsClient {}
