<?php

namespace MediaCloud\Vendor\Aws\LakeFormation;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Lake Formation** service.
 * @method \MediaCloud\Vendor\Aws\Result batchGrantPermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchGrantPermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchRevokePermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchRevokePermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDataLakeSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDataLakeSettingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEffectivePermissionsForPath(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEffectivePermissionsForPathAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result grantPermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise grantPermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putDataLakeSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putDataLakeSettingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result revokePermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise revokePermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateResourceAsync(array $args = [])
 */
class LakeFormationClient extends AwsClient {}
