<?php

namespace MediaCloud\Vendor\Aws\Honeycode;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Honeycode** service.
 * @method \MediaCloud\Vendor\Aws\Result batchCreateTableRows(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchCreateTableRowsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchDeleteTableRows(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchDeleteTableRowsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchUpdateTableRows(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchUpdateTableRowsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchUpsertTableRows(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchUpsertTableRowsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTableDataImportJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTableDataImportJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getScreenData(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getScreenDataAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result invokeScreenAutomation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise invokeScreenAutomationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTableColumns(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTableColumnsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTableRows(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTableRowsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTables(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result queryTableRows(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise queryTableRowsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startTableDataImportJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startTableDataImportJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class HoneycodeClient extends AwsClient {}
