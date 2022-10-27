<?php

namespace MediaCloud\Vendor\Aws\RecycleBin;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Recycle Bin** service.
 * @method \MediaCloud\Vendor\Aws\Result createRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRules(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRulesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateRuleAsync(array $args = [])
 */
class RecycleBinClient extends AwsClient {}
