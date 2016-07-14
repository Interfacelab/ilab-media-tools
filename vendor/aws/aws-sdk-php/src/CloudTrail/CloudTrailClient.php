<?php
namespace ILAB_Aws\CloudTrail;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CloudTrail** service.
 *
 * @method \ILAB_Aws\Result addTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result createTrail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTrailAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteTrail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTrailAsync(array $args = [])
 * @method \ILAB_Aws\Result describeTrails(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrailsAsync(array $args = [])
 * @method \ILAB_Aws\Result getTrailStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTrailStatusAsync(array $args = [])
 * @method \ILAB_Aws\Result listPublicKeys(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPublicKeysAsync(array $args = [])
 * @method \ILAB_Aws\Result listTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result lookupEvents(array $args = [])
 * @method \GuzzleHttp\Promise\Promise lookupEventsAsync(array $args = [])
 * @method \ILAB_Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result startLogging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startLoggingAsync(array $args = [])
 * @method \ILAB_Aws\Result stopLogging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopLoggingAsync(array $args = [])
 * @method \ILAB_Aws\Result updateTrail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateTrailAsync(array $args = [])
 */
class CloudTrailClient extends AwsClient {}
