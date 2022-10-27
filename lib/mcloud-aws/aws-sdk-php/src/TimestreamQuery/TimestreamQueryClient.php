<?php

namespace MediaCloud\Vendor\Aws\TimestreamQuery;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Timestream Query** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createScheduledQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createScheduledQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteScheduledQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteScheduledQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEndpoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEndpointsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScheduledQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScheduledQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result executeScheduledQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise executeScheduledQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listScheduledQueries(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listScheduledQueriesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result prepareQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise prepareQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result query(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise queryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateScheduledQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateScheduledQueryAsync(array $args = [])
 */
class TimestreamQueryClient extends AwsClient {}
