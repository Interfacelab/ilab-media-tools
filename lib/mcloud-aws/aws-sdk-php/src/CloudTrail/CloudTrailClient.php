<?php

namespace MediaCloud\Vendor\Aws\CloudTrail;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CloudTrail** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result addTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTrail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTrailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTrail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTrailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTrails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTrailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEventSelectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEventSelectorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getInsightSelectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getInsightSelectorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTrail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTrailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTrailStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTrailStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPublicKeys(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPublicKeysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTrails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTrailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result lookupEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise lookupEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putEventSelectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putEventSelectorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putInsightSelectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putInsightSelectorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startLogging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startLoggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopLogging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopLoggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTrail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTrailAsync(array $args = [])
 */
class CloudTrailClient extends AwsClient {}
