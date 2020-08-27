<?php

namespace MediaCloud\Vendor\Aws\MediaTailor;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS MediaTailor** service.
 * @method \MediaCloud\Vendor\Aws\Result deletePlaybackConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePlaybackConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPlaybackConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPlaybackConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPlaybackConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPlaybackConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putPlaybackConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putPlaybackConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class MediaTailorClient extends AwsClient {}
