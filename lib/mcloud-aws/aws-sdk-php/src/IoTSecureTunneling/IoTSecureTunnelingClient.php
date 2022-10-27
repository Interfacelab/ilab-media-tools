<?php

namespace MediaCloud\Vendor\Aws\IoTSecureTunneling;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Secure Tunneling** service.
 * @method \MediaCloud\Vendor\Aws\Result closeTunnel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise closeTunnelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTunnel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTunnelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTunnels(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTunnelsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result openTunnel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise openTunnelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class IoTSecureTunnelingClient extends AwsClient {}
