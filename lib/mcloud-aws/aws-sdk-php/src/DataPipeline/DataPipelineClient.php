<?php

namespace MediaCloud\Vendor\Aws\DataPipeline;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Data Pipeline** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result activatePipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise activatePipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result addTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deactivatePipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deactivatePipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePipeline(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeObjects(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeObjectsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePipelines(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePipelinesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result evaluateExpression(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise evaluateExpressionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPipelineDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPipelineDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPipelines(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result pollForTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise pollForTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putPipelineDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putPipelineDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result queryObjects(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise queryObjectsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result reportTaskProgress(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise reportTaskProgressAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result reportTaskRunnerHeartbeat(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise reportTaskRunnerHeartbeatAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setTaskStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setTaskStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result validatePipelineDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise validatePipelineDefinitionAsync(array $args = [])
 */
class DataPipelineClient extends AwsClient {}
