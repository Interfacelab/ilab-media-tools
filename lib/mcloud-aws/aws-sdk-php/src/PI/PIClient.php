<?php

namespace MediaCloud\Vendor\Aws\PI;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Performance Insights** service.
 * @method \MediaCloud\Vendor\Aws\Result describeDimensionKeys(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDimensionKeysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDimensionKeyDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDimensionKeyDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getResourceMetadata(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResourceMetadataAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getResourceMetrics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResourceMetricsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAvailableResourceDimensions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAvailableResourceDimensionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAvailableResourceMetrics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAvailableResourceMetricsAsync(array $args = [])
 */
class PIClient extends AwsClient {}
