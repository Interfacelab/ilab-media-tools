<?php

namespace MediaCloud\Vendor\Aws\IoTEventsData;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Events Data** service.
 * @method \MediaCloud\Vendor\Aws\Result batchPutMessage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchPutMessageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchUpdateDetector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchUpdateDetectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDetector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDetectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDetectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDetectorsAsync(array $args = [])
 */
class IoTEventsDataClient extends AwsClient {}
