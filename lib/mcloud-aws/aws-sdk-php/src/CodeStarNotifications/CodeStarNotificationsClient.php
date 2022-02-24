<?php

namespace MediaCloud\Vendor\Aws\CodeStarNotifications;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CodeStar Notifications** service.
 * @method \MediaCloud\Vendor\Aws\Result createNotificationRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createNotificationRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteNotificationRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteNotificationRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTarget(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTargetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeNotificationRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeNotificationRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEventTypes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEventTypesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listNotificationRules(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listNotificationRulesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTargets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTargetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result subscribe(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise subscribeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unsubscribe(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unsubscribeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateNotificationRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateNotificationRuleAsync(array $args = [])
 */
class CodeStarNotificationsClient extends AwsClient {}
