<?php

namespace MediaCloud\Vendor\Aws\DynamoDb;
use MediaCloud\Vendor\Aws\Api\Parser\Crc32ValidatingParser;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\ClientResolver;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\HandlerList;
use MediaCloud\Vendor\Aws\Middleware;
use MediaCloud\Vendor\Aws\RetryMiddleware;
use MediaCloud\Vendor\Aws\RetryMiddlewareV2;

/**
 * This client is used to interact with the **Amazon DynamoDB** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result batchGetItem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchGetItemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchWriteItem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchWriteItemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteItem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteItemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getItem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getItemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTables(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putItem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putItemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result query(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise queryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result scan(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise scanAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateItem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateItemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchExecuteStatement(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result createBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result createGlobalTable(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGlobalTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result deleteBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeContinuousBackups(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeContinuousBackupsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeContributorInsights(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeContributorInsightsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeEndpoints(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEndpointsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeExport(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeExportAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeGlobalTable(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeGlobalTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeGlobalTableSettings(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeGlobalTableSettingsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeLimits(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeLimitsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeTableReplicaAutoScaling(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTableReplicaAutoScalingAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result describeTimeToLive(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTimeToLiveAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result disableKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disableKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result enableKinesisStreamingDestination(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise enableKinesisStreamingDestinationAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result executeStatement(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result executeTransaction(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise executeTransactionAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result exportTableToPointInTime(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportTableToPointInTimeAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result listBackups(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBackupsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result listContributorInsights(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listContributorInsightsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result listExports(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listExportsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result listGlobalTables(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGlobalTablesAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result listTagsOfResource(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsOfResourceAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result restoreTableFromBackup(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreTableFromBackupAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result restoreTableToPointInTime(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreTableToPointInTimeAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result transactGetItems(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise transactGetItemsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result transactWriteItems(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise transactWriteItemsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result updateContinuousBackups(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateContinuousBackupsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result updateContributorInsights(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateContributorInsightsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result updateGlobalTable(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateGlobalTableAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result updateGlobalTableSettings(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateGlobalTableSettingsAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result updateTableReplicaAutoScaling(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTableReplicaAutoScalingAsync(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\Aws\Result updateTimeToLive(array $args = []) (supported in versions 2012-08-10)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTimeToLiveAsync(array $args = []) (supported in versions 2012-08-10)
 */
class DynamoDbClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 10;
        $args['retries']['fn'] = [__CLASS__, '_applyRetryConfig'];
        $args['api_provider']['fn'] = [__CLASS__, '_applyApiProvider'];

        return $args;
    }

    /**
     * Convenience method for instantiating and registering the DynamoDB
     * Session handler with this DynamoDB client object.
     *
     * @param array $config Array of options for the session handler factory
     *
     * @return SessionHandler
     */
    public function registerSessionHandler(array $config = [])
    {
        $handler = SessionHandler::fromClient($this, $config);
        $handler->register();

        return $handler;
    }

    /** @internal */
    public static function _applyRetryConfig($value, array &$args, HandlerList $list)
    {
        if ($value) {
            $config = \MediaCloud\Vendor\Aws\Retry\ConfigurationProvider::unwrap($value);

            if ($config->getMode() === 'legacy') {
                $list->appendSign(
                    Middleware::retry(
                        RetryMiddleware::createDefaultDecider(
                            $config->getMaxAttempts() - 1,
                            ['error_codes' => ['TransactionInProgressException']]
                        ),
                        function ($retries) {
                            return $retries
                                ? RetryMiddleware::exponentialDelay($retries) / 2
                                : 0;
                        },
                        isset($args['stats']['retries'])
                            ? (bool)$args['stats']['retries']
                            : false
                    ),
                    'retry'
                );
            } else {
                $list->appendSign(
                    RetryMiddlewareV2::wrap(
                        $config,
                        [
                            'collect_stats' => $args['stats']['retries'],
                            'transient_error_codes' => ['TransactionInProgressException']
                        ]
                    ),
                    'retry'
                );
            }
        }
    }

    /** @internal */
    public static function _applyApiProvider($value, array &$args, HandlerList $list)
    {
        ClientResolver::_apply_api_provider($value, $args);
        $args['parser'] = new Crc32ValidatingParser($args['parser']);
    }
}
