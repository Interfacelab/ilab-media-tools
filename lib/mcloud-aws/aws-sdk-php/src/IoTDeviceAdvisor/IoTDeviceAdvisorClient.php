<?php

namespace MediaCloud\Vendor\Aws\IoTDeviceAdvisor;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Core Device Advisor** service.
 * @method \MediaCloud\Vendor\Aws\Result createSuiteDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createSuiteDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteSuiteDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteSuiteDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSuiteDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSuiteDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSuiteRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSuiteRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSuiteRunReport(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSuiteRunReportAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSuiteDefinitions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSuiteDefinitionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSuiteRuns(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSuiteRunsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startSuiteRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startSuiteRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopSuiteRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopSuiteRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateSuiteDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateSuiteDefinitionAsync(array $args = [])
 */
class IoTDeviceAdvisorClient extends AwsClient {}
