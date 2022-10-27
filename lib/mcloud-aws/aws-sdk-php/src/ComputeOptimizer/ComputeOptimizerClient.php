<?php

namespace MediaCloud\Vendor\Aws\ComputeOptimizer;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Compute Optimizer** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteRecommendationPreferences(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteRecommendationPreferencesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRecommendationExportJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRecommendationExportJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportAutoScalingGroupRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportAutoScalingGroupRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportEBSVolumeRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportEBSVolumeRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportEC2InstanceRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportEC2InstanceRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportLambdaFunctionRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportLambdaFunctionRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAutoScalingGroupRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAutoScalingGroupRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEBSVolumeRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEBSVolumeRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEC2InstanceRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEC2InstanceRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEC2RecommendationProjectedMetrics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEC2RecommendationProjectedMetricsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEffectiveRecommendationPreferences(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEffectiveRecommendationPreferencesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEnrollmentStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEnrollmentStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEnrollmentStatusesForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEnrollmentStatusesForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLambdaFunctionRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLambdaFunctionRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRecommendationPreferences(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRecommendationPreferencesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRecommendationSummaries(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRecommendationSummariesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putRecommendationPreferences(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putRecommendationPreferencesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnrollmentStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnrollmentStatusAsync(array $args = [])
 */
class ComputeOptimizerClient extends AwsClient {}
