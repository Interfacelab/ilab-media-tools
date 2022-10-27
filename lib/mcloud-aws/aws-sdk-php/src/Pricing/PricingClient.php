<?php

namespace MediaCloud\Vendor\Aws\Pricing;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Price List Service** service.
 * @method \MediaCloud\Vendor\Aws\Result describeServices(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeServicesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAttributeValues(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAttributeValuesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getProducts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getProductsAsync(array $args = [])
 */
class PricingClient extends AwsClient {}
