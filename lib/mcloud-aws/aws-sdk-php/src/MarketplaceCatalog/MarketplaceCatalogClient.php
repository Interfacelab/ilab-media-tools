<?php

namespace MediaCloud\Vendor\Aws\MarketplaceCatalog;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Marketplace Catalog Service** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelChangeSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelChangeSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeChangeSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeChangeSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEntity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEntityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listChangeSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listChangeSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEntities(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEntitiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startChangeSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startChangeSetAsync(array $args = [])
 */
class MarketplaceCatalogClient extends AwsClient {}
