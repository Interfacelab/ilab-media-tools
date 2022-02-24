<?php

namespace MediaCloud\Vendor\Aws\MediaPackageVod;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elemental MediaPackage VOD** service.
 * @method \MediaCloud\Vendor\Aws\Result configureLogs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise configureLogsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createAsset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAssetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPackagingConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPackagingConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPackagingGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPackagingGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAsset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAssetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePackagingConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePackagingConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePackagingGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePackagingGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAsset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAssetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePackagingConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePackagingConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePackagingGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePackagingGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAssets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAssetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPackagingConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPackagingConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPackagingGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPackagingGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updatePackagingGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePackagingGroupAsync(array $args = [])
 */
class MediaPackageVodClient extends AwsClient {}
