<?php

namespace MediaCloud\Vendor\Aws\Firehose;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Firehose** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result createDeliveryStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDeliveryStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDeliveryStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDeliveryStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDeliveryStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDeliveryStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDeliveryStreams(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDeliveryStreamsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForDeliveryStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForDeliveryStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putRecord(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putRecordAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putRecordBatch(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putRecordBatchAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startDeliveryStreamEncryption(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDeliveryStreamEncryptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopDeliveryStreamEncryption(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopDeliveryStreamEncryptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagDeliveryStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagDeliveryStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagDeliveryStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagDeliveryStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDestinationAsync(array $args = [])
 */
class FirehoseClient extends AwsClient {}
