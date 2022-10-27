<?php

namespace MediaCloud\Vendor\Aws\IdentityStore;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS SSO Identity Store** service.
 * @method \MediaCloud\Vendor\Aws\Result describeGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeUser(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeUserAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listUsers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listUsersAsync(array $args = [])
 */
class IdentityStoreClient extends AwsClient {}
