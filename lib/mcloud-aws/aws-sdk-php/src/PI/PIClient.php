<?php

namespace MediaCloud\Vendor\Aws\PI;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Performance Insights** service.
 * @method \MediaCloud\Vendor\Aws\Result describeDimensionKeys(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDimensionKeysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getResourceMetrics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResourceMetricsAsync(array $args = [])
 */
class PIClient extends AwsClient {}
