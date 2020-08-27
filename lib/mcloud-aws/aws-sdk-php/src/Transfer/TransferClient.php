<?php

namespace MediaCloud\Vendor\Aws\Transfer;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Transfer for SFTP** service.
 * @method \MediaCloud\Vendor\Aws\Result createServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createUser(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createUserAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteSshPublicKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteSshPublicKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteUser(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteUserAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSecurityPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSecurityPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeUser(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeUserAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importSshPublicKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importSshPublicKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSecurityPolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSecurityPoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listServers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listServersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listUsers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listUsersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result testIdentityProvider(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise testIdentityProviderAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateUser(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateUserAsync(array $args = [])
 */
class TransferClient extends AwsClient {}
