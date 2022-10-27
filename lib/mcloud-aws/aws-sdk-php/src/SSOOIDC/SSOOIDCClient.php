<?php

namespace MediaCloud\Vendor\Aws\SSOOIDC;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS SSO OIDC** service.
 * @method \MediaCloud\Vendor\Aws\Result createToken(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTokenAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerClient(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerClientAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startDeviceAuthorization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDeviceAuthorizationAsync(array $args = [])
 */
class SSOOIDCClient extends AwsClient {}
