<?php

namespace MediaCloud\Vendor\Aws\Account;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Account** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteAlternateContact(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAlternateContactAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAlternateContact(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAlternateContactAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putAlternateContact(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putAlternateContactAsync(array $args = [])
 */
class AccountClient extends AwsClient {}
