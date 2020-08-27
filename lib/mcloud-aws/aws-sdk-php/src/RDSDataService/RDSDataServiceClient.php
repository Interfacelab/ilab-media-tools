<?php

namespace MediaCloud\Vendor\Aws\RDSDataService;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS RDS DataService** service.
 * @method \MediaCloud\Vendor\Aws\Result batchExecuteStatement(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result beginTransaction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise beginTransactionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result commitTransaction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise commitTransactionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result executeSql(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise executeSqlAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result executeStatement(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rollbackTransaction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rollbackTransactionAsync(array $args = [])
 */
class RDSDataServiceClient extends AwsClient {}
