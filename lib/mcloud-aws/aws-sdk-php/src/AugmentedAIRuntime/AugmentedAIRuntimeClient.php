<?php

namespace MediaCloud\Vendor\Aws\AugmentedAIRuntime;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Augmented AI Runtime** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteHumanLoop(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHumanLoopAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeHumanLoop(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeHumanLoopAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHumanLoops(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHumanLoopsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startHumanLoop(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startHumanLoopAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopHumanLoop(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopHumanLoopAsync(array $args = [])
 */
class AugmentedAIRuntimeClient extends AwsClient {}
