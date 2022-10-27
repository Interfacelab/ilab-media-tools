<?php

namespace MediaCloud\Vendor\Aws\MediaPackage;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elemental MediaPackage** service.
 * @method \MediaCloud\Vendor\Aws\Result configureLogs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise configureLogsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHarvestJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHarvestJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createOriginEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createOriginEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteOriginEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteOriginEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeHarvestJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeHarvestJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOriginEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOriginEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listChannels(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listChannelsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHarvestJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHarvestJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listOriginEndpoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listOriginEndpointsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rotateChannelCredentials(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rotateChannelCredentialsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rotateIngestEndpointCredentials(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rotateIngestEndpointCredentialsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateChannel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateChannelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateOriginEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateOriginEndpointAsync(array $args = [])
 */
class MediaPackageClient extends AwsClient {}
