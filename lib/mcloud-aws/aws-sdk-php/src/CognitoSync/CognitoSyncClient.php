<?php

namespace MediaCloud\Vendor\Aws\CognitoSync;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Cognito Sync** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result bulkPublish(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise bulkPublishAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeIdentityPoolUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeIdentityPoolUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeIdentityUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeIdentityUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBulkPublishDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBulkPublishDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCognitoEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCognitoEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getIdentityPoolConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getIdentityPoolConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDatasets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDatasetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listIdentityPoolUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listIdentityPoolUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRecords(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRecordsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerDevice(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerDeviceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setCognitoEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setCognitoEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setIdentityPoolConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setIdentityPoolConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result subscribeToDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise subscribeToDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unsubscribeFromDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unsubscribeFromDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateRecords(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateRecordsAsync(array $args = [])
 */
class CognitoSyncClient extends AwsClient {}
