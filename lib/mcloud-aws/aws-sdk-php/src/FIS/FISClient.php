<?php

namespace MediaCloud\Vendor\Aws\FIS;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Fault Injection Simulator** service.
 * @method \MediaCloud\Vendor\Aws\Result createExperimentTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createExperimentTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteExperimentTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteExperimentTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getActionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getExperiment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getExperimentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getExperimentTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getExperimentTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTargetResourceType(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTargetResourceTypeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listActions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listActionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listExperimentTemplates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listExperimentTemplatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listExperiments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listExperimentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTargetResourceTypes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTargetResourceTypesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startExperiment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startExperimentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopExperiment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopExperimentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateExperimentTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateExperimentTemplateAsync(array $args = [])
 */
class FISClient extends AwsClient {}
