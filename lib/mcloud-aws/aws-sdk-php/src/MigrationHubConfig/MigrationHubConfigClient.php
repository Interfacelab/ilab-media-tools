<?php

namespace MediaCloud\Vendor\Aws\MigrationHubConfig;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Migration Hub Config** service.
 * @method \MediaCloud\Vendor\Aws\Result createHomeRegionControl(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHomeRegionControlAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeHomeRegionControls(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeHomeRegionControlsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHomeRegion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHomeRegionAsync(array $args = [])
 */
class MigrationHubConfigClient extends AwsClient {}
