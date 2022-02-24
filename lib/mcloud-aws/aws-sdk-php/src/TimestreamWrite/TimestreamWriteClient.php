<?php

namespace MediaCloud\Vendor\Aws\TimestreamWrite;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Timestream Write** service.
 * @method \MediaCloud\Vendor\Aws\Result createDatabase(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDatabaseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDatabase(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDatabaseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDatabase(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDatabaseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEndpoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEndpointsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDatabases(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDatabasesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTables(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDatabase(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDatabaseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result writeRecords(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise writeRecordsAsync(array $args = [])
 */
class TimestreamWriteClient extends AwsClient {}
