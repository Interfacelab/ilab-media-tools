<?php

namespace MediaCloud\Vendor\Aws\IotDataPlane;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Data Plane** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result deleteThingShadow(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteThingShadowAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRetainedMessage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRetainedMessageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getThingShadow(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getThingShadowAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listNamedShadowsForThing(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listNamedShadowsForThingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRetainedMessages(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRetainedMessagesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result publish(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateThingShadow(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateThingShadowAsync(array $args = [])
 */
class IotDataPlaneClient extends AwsClient {}
