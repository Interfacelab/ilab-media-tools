<?php

namespace MediaCloud\Vendor\Aws\KinesisVideo;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Kinesis Video Streams** service.
 * @method \MediaCloud\Vendor\Aws\Result createSignalingChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createSignalingChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteSignalingChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteSignalingChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSignalingChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSignalingChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDataEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDataEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSignalingChannelEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSignalingChannelEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSignalingChannels(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSignalingChannelsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listStreams(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDataRetention(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDataRetentionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateSignalingChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateSignalingChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateStreamAsync(array $args = [])
 */
class KinesisVideoClient extends AwsClient {}
