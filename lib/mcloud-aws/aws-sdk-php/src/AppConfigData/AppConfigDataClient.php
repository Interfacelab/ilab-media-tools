<?php

namespace MediaCloud\Vendor\Aws\AppConfigData;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS AppConfig Data** service.
 * @method \MediaCloud\Vendor\Aws\Result getLatestConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLatestConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startConfigurationSession(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startConfigurationSessionAsync(array $args = [])
 */
class AppConfigDataClient extends AwsClient {}
