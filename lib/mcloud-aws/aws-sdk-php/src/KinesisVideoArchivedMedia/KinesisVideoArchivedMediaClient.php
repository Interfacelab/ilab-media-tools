<?php

namespace MediaCloud\Vendor\Aws\KinesisVideoArchivedMedia;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Video Streams Archived Media** service.
 * @method \MediaCloud\Vendor\Aws\Result getClip(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getClipAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDASHStreamingSessionURL(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDASHStreamingSessionURLAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHLSStreamingSessionURL(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHLSStreamingSessionURLAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMediaForFragmentList(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMediaForFragmentListAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFragments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFragmentsAsync(array $args = [])
 */
class KinesisVideoArchivedMediaClient extends AwsClient {}
