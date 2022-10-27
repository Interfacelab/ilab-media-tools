<?php

namespace MediaCloud\Vendor\Aws\ResourceGroupsTaggingAPI;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Resource Groups Tagging API** service.
 * @method \MediaCloud\Vendor\Aws\Result describeReportCreation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReportCreationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getComplianceSummary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getComplianceSummaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTagKeys(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTagKeysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTagValues(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTagValuesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startReportCreation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startReportCreationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourcesAsync(array $args = [])
 */
class ResourceGroupsTaggingAPIClient extends AwsClient {}
