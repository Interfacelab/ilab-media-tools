<?php

namespace MediaCloud\Vendor\Aws\PinpointSMSVoice;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Pinpoint SMS and Voice Service** service.
 * @method \MediaCloud\Vendor\Aws\Result createConfigurationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConfigurationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createConfigurationSetEventDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConfigurationSetEventDestinationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConfigurationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConfigurationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConfigurationSetEventDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConfigurationSetEventDestinationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getConfigurationSetEventDestinations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getConfigurationSetEventDestinationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listConfigurationSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listConfigurationSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendVoiceMessage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendVoiceMessageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConfigurationSetEventDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConfigurationSetEventDestinationAsync(array $args = [])
 */
class PinpointSMSVoiceClient extends AwsClient {}
