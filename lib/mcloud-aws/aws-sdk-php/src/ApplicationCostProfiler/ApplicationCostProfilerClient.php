<?php

namespace MediaCloud\Vendor\Aws\ApplicationCostProfiler;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Application Cost Profiler** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteReportDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteReportDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getReportDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getReportDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importApplicationUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importApplicationUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listReportDefinitions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listReportDefinitionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putReportDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putReportDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateReportDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateReportDefinitionAsync(array $args = [])
 */
class ApplicationCostProfilerClient extends AwsClient {}
