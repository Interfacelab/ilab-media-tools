<?php

namespace MediaCloud\Vendor\Aws\Health;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Health APIs and Notifications** service.
 * @method \MediaCloud\Vendor\Aws\Result describeAffectedAccountsForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAffectedAccountsForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAffectedEntities(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAffectedEntitiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAffectedEntitiesForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAffectedEntitiesForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEntityAggregates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEntityAggregatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventAggregates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventAggregatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventDetailsForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventDetailsForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventTypes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventTypesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventsForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventsForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeHealthServiceStatusForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeHealthServiceStatusForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disableHealthServiceAccessForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disableHealthServiceAccessForOrganizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result enableHealthServiceAccessForOrganization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise enableHealthServiceAccessForOrganizationAsync(array $args = [])
 */
class HealthClient extends AwsClient {}
