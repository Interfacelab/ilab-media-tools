<?php

namespace MediaCloud\Vendor\Aws\SSO;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Single Sign-On** service.
 * @method \MediaCloud\Vendor\Aws\Result getRoleCredentials(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRoleCredentialsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAccountRoles(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAccountRolesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAccounts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAccountsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result logout(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise logoutAsync(array $args = [])
 */
class SSOClient extends AwsClient {}
