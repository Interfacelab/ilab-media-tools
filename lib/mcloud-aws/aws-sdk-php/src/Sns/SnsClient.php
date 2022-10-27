<?php

namespace MediaCloud\Vendor\Aws\Sns;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Simple Notification Service (Amazon SNS)**.
 *
 * @method \MediaCloud\Vendor\Aws\Result addPermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addPermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result checkIfPhoneNumberIsOptedOut(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise checkIfPhoneNumberIsOptedOutAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result confirmSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise confirmSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPlatformApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPlatformApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPlatformEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPlatformEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createSMSSandboxPhoneNumber(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createSMSSandboxPhoneNumberAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTopic(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTopicAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePlatformApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePlatformApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteSMSSandboxPhoneNumber(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteSMSSandboxPhoneNumberAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTopic(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTopicAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEndpointAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEndpointAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPlatformApplicationAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPlatformApplicationAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSMSAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSMSAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSMSSandboxAccountStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSMSSandboxAccountStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSubscriptionAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSubscriptionAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTopicAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTopicAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEndpointsByPlatformApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEndpointsByPlatformApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listOriginationNumbers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listOriginationNumbersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPhoneNumbersOptedOut(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPhoneNumbersOptedOutAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPlatformApplications(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPlatformApplicationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSMSSandboxPhoneNumbers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSMSSandboxPhoneNumbersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSubscriptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSubscriptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSubscriptionsByTopic(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSubscriptionsByTopicAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTopics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTopicsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result optInPhoneNumber(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise optInPhoneNumberAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result publish(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result publishBatch(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishBatchAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removePermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removePermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setEndpointAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setEndpointAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setPlatformApplicationAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setPlatformApplicationAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setSMSAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setSMSAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setSubscriptionAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setSubscriptionAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setTopicAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setTopicAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result subscribe(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise subscribeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unsubscribe(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unsubscribeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result verifySMSSandboxPhoneNumber(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise verifySMSSandboxPhoneNumberAsync(array $args = [])
 */
class SnsClient extends AwsClient {}
