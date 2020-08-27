<?php

namespace MediaCloud\Vendor\Aws\ComputeOptimizer;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Compute Optimizer** service.
 * @method \MediaCloud\Vendor\Aws\Result describeRecommendationExportJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRecommendationExportJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportAutoScalingGroupRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportAutoScalingGroupRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportEC2InstanceRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportEC2InstanceRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAutoScalingGroupRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAutoScalingGroupRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEC2InstanceRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEC2InstanceRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEC2RecommendationProjectedMetrics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEC2RecommendationProjectedMetricsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEnrollmentStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEnrollmentStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRecommendationSummaries(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRecommendationSummariesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnrollmentStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnrollmentStatusAsync(array $args = [])
 */
class ComputeOptimizerClient extends AwsClient {}
