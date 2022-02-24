<?php

namespace MediaCloud\Vendor\Aws\CloudHSMV2;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CloudHSM V2** service.
 * @method \MediaCloud\Vendor\Aws\Result copyBackupToRegion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyBackupToRegionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHsm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHsmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHsm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHsmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeBackups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeBackupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeClusters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeClustersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result initializeCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise initializeClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyBackupAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyBackupAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class CloudHSMV2Client extends AwsClient {}
