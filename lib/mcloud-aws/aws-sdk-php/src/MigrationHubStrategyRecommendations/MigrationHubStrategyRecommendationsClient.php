<?php

namespace MediaCloud\Vendor\Aws\MigrationHubStrategyRecommendations;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Migration Hub Strategy Recommendations** service.
 * @method \MediaCloud\Vendor\Aws\Result getApplicationComponentDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getApplicationComponentDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getApplicationComponentStrategies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getApplicationComponentStrategiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAssessment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAssessmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getImportFileTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getImportFileTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPortfolioPreferences(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPortfolioPreferencesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPortfolioSummary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPortfolioSummaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRecommendationReportDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRecommendationReportDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getServerDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getServerDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getServerStrategies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getServerStrategiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listApplicationComponents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listApplicationComponentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCollectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCollectorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listImportFileTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listImportFileTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listServers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listServersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putPortfolioPreferences(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putPortfolioPreferencesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startAssessment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startAssessmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startImportFileTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startImportFileTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startRecommendationReportGeneration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startRecommendationReportGenerationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopAssessment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopAssessmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApplicationComponentConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateApplicationComponentConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateServerConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateServerConfigAsync(array $args = [])
 */
class MigrationHubStrategyRecommendationsClient extends AwsClient {}
