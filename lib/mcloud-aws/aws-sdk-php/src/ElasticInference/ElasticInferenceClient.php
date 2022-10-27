<?php

namespace MediaCloud\Vendor\Aws\ElasticInference;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic  Inference** service.
 * @method \MediaCloud\Vendor\Aws\Result describeAcceleratorOfferings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAcceleratorOfferingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAcceleratorTypes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAcceleratorTypesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAccelerators(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAcceleratorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class ElasticInferenceClient extends AwsClient {}
