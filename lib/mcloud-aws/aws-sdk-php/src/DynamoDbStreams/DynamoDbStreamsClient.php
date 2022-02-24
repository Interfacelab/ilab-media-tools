<?php

namespace MediaCloud\Vendor\Aws\DynamoDbStreams;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\DynamoDb\DynamoDbClient;

/**
 * This client is used to interact with the **Amazon DynamoDb Streams** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result describeStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRecords(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getShardIterator(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listStreams(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 */
class DynamoDbStreamsClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 11;
        $args['retries']['fn'] = [DynamoDbClient::class, '_applyRetryConfig'];

        return $args;
    }
}