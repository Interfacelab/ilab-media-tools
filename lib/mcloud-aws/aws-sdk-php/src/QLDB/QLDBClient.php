<?php

namespace MediaCloud\Vendor\Aws\QLDB;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon QLDB** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelJournalKinesisStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelJournalKinesisStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createLedger(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createLedgerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteLedger(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteLedgerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJournalKinesisStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJournalKinesisStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJournalS3Export(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJournalS3ExportAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeLedger(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeLedgerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportJournalToS3(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportJournalToS3Async(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDigest(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDigestAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRevision(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRevisionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJournalKinesisStreamsForLedger(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJournalKinesisStreamsForLedgerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJournalS3Exports(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJournalS3ExportsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJournalS3ExportsForLedger(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJournalS3ExportsForLedgerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listLedgers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listLedgersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result streamJournalToKinesis(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise streamJournalToKinesisAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateLedger(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateLedgerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateLedgerPermissionsMode(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateLedgerPermissionsModeAsync(array $args = [])
 */
class QLDBClient extends AwsClient {}
