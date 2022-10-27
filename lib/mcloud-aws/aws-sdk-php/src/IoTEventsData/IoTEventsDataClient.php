<?php

namespace MediaCloud\Vendor\Aws\IoTEventsData;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Events Data** service.
 * @method \MediaCloud\Vendor\Aws\Result batchAcknowledgeAlarm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchAcknowledgeAlarmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchDisableAlarm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchDisableAlarmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchEnableAlarm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchEnableAlarmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchPutMessage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchPutMessageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchResetAlarm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchResetAlarmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchSnoozeAlarm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchSnoozeAlarmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result batchUpdateDetector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchUpdateDetectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAlarm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAlarmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDetector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDetectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAlarms(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAlarmsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDetectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDetectorsAsync(array $args = [])
 */
class IoTEventsDataClient extends AwsClient {}
