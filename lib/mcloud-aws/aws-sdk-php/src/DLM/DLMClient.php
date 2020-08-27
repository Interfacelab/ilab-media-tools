<?php

namespace MediaCloud\Vendor\Aws\DLM;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Data Lifecycle Manager** service.
 * @method \MediaCloud\Vendor\Aws\Result createLifecyclePolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createLifecyclePolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteLifecyclePolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteLifecyclePolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLifecyclePolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLifecyclePoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLifecyclePolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLifecyclePolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateLifecyclePolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateLifecyclePolicyAsync(array $args = [])
 */
class DLMClient extends AwsClient {}
