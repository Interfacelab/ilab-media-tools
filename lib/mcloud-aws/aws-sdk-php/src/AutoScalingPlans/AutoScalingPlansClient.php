<?php

namespace MediaCloud\Vendor\Aws\AutoScalingPlans;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Auto Scaling Plans** service.
 * @method \MediaCloud\Vendor\Aws\Result createScalingPlan(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createScalingPlanAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteScalingPlan(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteScalingPlanAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScalingPlanResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScalingPlanResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScalingPlans(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScalingPlansAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getScalingPlanResourceForecastData(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getScalingPlanResourceForecastDataAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateScalingPlan(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateScalingPlanAsync(array $args = [])
 */
class AutoScalingPlansClient extends AwsClient {}
