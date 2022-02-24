<?php

namespace MediaCloud\Vendor\Aws\LexRuntimeV2;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Lex Runtime V2** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteSession(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteSessionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSession(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSessionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putSession(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putSessionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result recognizeText(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise recognizeTextAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result recognizeUtterance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise recognizeUtteranceAsync(array $args = [])
 */
class LexRuntimeV2Client extends AwsClient {}
