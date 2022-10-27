<?php

namespace MediaCloud\Vendor\Aws\RedshiftDataAPIService;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Redshift Data API Service** service.
 * @method \MediaCloud\Vendor\Aws\Result batchExecuteStatement(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result cancelStatement(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelStatementAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeStatement(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeStatementAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTable(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTableAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result executeStatement(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getStatementResult(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getStatementResultAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDatabases(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDatabasesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSchemas(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSchemasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listStatements(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listStatementsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTables(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 */
class RedshiftDataAPIServiceClient extends AwsClient {}
