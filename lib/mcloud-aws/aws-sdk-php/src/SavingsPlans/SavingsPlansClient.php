<?php

namespace MediaCloud\Vendor\Aws\SavingsPlans;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Savings Plans** service.
 * @method \MediaCloud\Vendor\Aws\Result createSavingsPlan(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createSavingsPlanAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteQueuedSavingsPlan(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteQueuedSavingsPlanAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSavingsPlanRates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSavingsPlanRatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSavingsPlans(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSavingsPlansAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSavingsPlansOfferingRates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSavingsPlansOfferingRatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSavingsPlansOfferings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSavingsPlansOfferingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SavingsPlansClient extends AwsClient {}
