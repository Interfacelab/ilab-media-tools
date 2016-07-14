<?php
namespace ILAB_Aws\DataPipeline;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Data Pipeline** service.
 *
 * @method \ILAB_Aws\Result activatePipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise activatePipelineAsync(array $args = [])
 * @method \ILAB_Aws\Result addTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result createPipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \ILAB_Aws\Result deactivatePipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deactivatePipelineAsync(array $args = [])
 * @method \ILAB_Aws\Result deletePipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \ILAB_Aws\Result describeObjects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeObjectsAsync(array $args = [])
 * @method \ILAB_Aws\Result describePipelines(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describePipelinesAsync(array $args = [])
 * @method \ILAB_Aws\Result evaluateExpression(array $args = [])
 * @method \GuzzleHttp\Promise\Promise evaluateExpressionAsync(array $args = [])
 * @method \ILAB_Aws\Result getPipelineDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPipelineDefinitionAsync(array $args = [])
 * @method \ILAB_Aws\Result listPipelines(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \ILAB_Aws\Result pollForTask(array $args = [])
 * @method \GuzzleHttp\Promise\Promise pollForTaskAsync(array $args = [])
 * @method \ILAB_Aws\Result putPipelineDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putPipelineDefinitionAsync(array $args = [])
 * @method \ILAB_Aws\Result queryObjects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise queryObjectsAsync(array $args = [])
 * @method \ILAB_Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result reportTaskProgress(array $args = [])
 * @method \GuzzleHttp\Promise\Promise reportTaskProgressAsync(array $args = [])
 * @method \ILAB_Aws\Result reportTaskRunnerHeartbeat(array $args = [])
 * @method \GuzzleHttp\Promise\Promise reportTaskRunnerHeartbeatAsync(array $args = [])
 * @method \ILAB_Aws\Result setStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise setStatusAsync(array $args = [])
 * @method \ILAB_Aws\Result setTaskStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise setTaskStatusAsync(array $args = [])
 * @method \ILAB_Aws\Result validatePipelineDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise validatePipelineDefinitionAsync(array $args = [])
 */
class DataPipelineClient extends AwsClient {}
