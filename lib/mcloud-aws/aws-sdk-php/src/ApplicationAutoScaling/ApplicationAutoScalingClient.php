<?php

namespace MediaCloud\Vendor\Aws\ApplicationAutoScaling;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Application Auto Scaling** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteScalingPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteScalingPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteScheduledAction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteScheduledActionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterScalableTarget(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterScalableTargetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScalableTargets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScalableTargetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScalingActivities(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScalingActivitiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScalingPolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScalingPoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeScheduledActions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeScheduledActionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putScalingPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putScalingPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putScheduledAction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putScheduledActionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerScalableTarget(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerScalableTargetAsync(array $args = [])
 */
class ApplicationAutoScalingClient extends AwsClient {}
