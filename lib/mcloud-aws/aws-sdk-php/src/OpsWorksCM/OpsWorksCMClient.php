<?php

namespace MediaCloud\Vendor\Aws\OpsWorksCM;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS OpsWorks for Chef Automate** service.
 * @method \MediaCloud\Vendor\Aws\Result associateNode(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateNodeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAccountAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAccountAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeBackups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeBackupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeNodeAssociationStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeNodeAssociationStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeServers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeServersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateNode(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateNodeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportServerEngineAttribute(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportServerEngineAttributeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startMaintenance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startMaintenanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateServerEngineAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateServerEngineAttributesAsync(array $args = [])
 */
class OpsWorksCMClient extends AwsClient {}
