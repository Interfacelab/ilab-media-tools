<?php
namespace ILAB_Aws\DynamoDb;

use ILAB_Aws\Api\Parser\Crc32ValidatingParser;
use ILAB_Aws\AwsClient;
use ILAB_Aws\ClientResolver;
use ILAB_Aws\HandlerList;
use ILAB_Aws\Middleware;
use ILAB_Aws\RetryMiddleware;

/**
 * This client is used to interact with the **Amazon DynamoDB** service.
 *
 * @method \ILAB_Aws\Result batchGetItem(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetItemAsync(array $args = [])
 * @method \ILAB_Aws\Result batchWriteItem(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchWriteItemAsync(array $args = [])
 * @method \ILAB_Aws\Result createTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteItem(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteItemAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \ILAB_Aws\Result describeLimits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeLimitsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \ILAB_Aws\Result getItem(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getItemAsync(array $args = [])
 * @method \ILAB_Aws\Result listTables(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \ILAB_Aws\Result putItem(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putItemAsync(array $args = [])
 * @method \ILAB_Aws\Result query(array $args = [])
 * @method \GuzzleHttp\Promise\Promise queryAsync(array $args = [])
 * @method \ILAB_Aws\Result scan(array $args = [])
 * @method \GuzzleHttp\Promise\Promise scanAsync(array $args = [])
 * @method \ILAB_Aws\Result updateItem(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateItemAsync(array $args = [])
 * @method \ILAB_Aws\Result updateTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
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
        if (!$value) {
            return;
        }

        $list->appendSign(
            Middleware::retry(
                RetryMiddleware::createDefaultDecider($value),
                function ($retries) {
                    return $retries
                        ? RetryMiddleware::exponentialDelay($retries) / 2
                        : 0;
                },
                isset($args['stats']['retries'])
                    ? (bool) $args['stats']['retries']
                    : false
            ),
            'retry'
        );
    }

    /** @internal */
    public static function _applyApiProvider($value, array &$args, HandlerList $list)
    {
        ClientResolver::_apply_api_provider($value, $args, $list);
        $args['parser'] = new Crc32ValidatingParser($args['parser']);
    }
}
