<?php

namespace MediaCloud\Vendor\Aws\ElasticTranscoder;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic Transcoder** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result cancelJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPreset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPresetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePreset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePresetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJobsByPipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJobsByPipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJobsByStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJobsByStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPipelines(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPresets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPresetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result readJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise readJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result readPipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise readPipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result readPreset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise readPresetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result testRole(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise testRoleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updatePipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updatePipelineNotifications(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePipelineNotificationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updatePipelineStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePipelineStatusAsync(array $args = [])
 */
class ElasticTranscoderClient extends AwsClient {}
