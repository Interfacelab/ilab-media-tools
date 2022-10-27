<?php

namespace MediaCloud\Vendor\Aws\EBS;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic Block Store** service.
 * @method \MediaCloud\Vendor\Aws\Result completeSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise completeSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSnapshotBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSnapshotBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listChangedBlocks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listChangedBlocksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSnapshotBlocks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSnapshotBlocksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putSnapshotBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putSnapshotBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startSnapshotAsync(array $args = [])
 */
class EBSClient extends AwsClient {}
