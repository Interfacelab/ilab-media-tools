<?php

namespace MediaCloud\Vendor\Aws\MarketplaceMetering;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWSMarketplace Metering** service.
 * @method \MediaCloud\Vendor\Aws\Result batchMeterUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchMeterUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result meterUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise meterUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerUsage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerUsageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result resolveCustomer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise resolveCustomerAsync(array $args = [])
 */
class MarketplaceMeteringClient extends AwsClient {}
