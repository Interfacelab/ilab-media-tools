<?php

namespace MediaCloud\Vendor\Aws\LicenseManager;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS License Manager** service.
 * @method \MediaCloud\Vendor\Aws\Result createLicenseConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createLicenseConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteLicenseConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteLicenseConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLicenseConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLicenseConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getServiceSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getServiceSettingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAssociationsForLicenseConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAssociationsForLicenseConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFailuresForLicenseConfigurationOperations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFailuresForLicenseConfigurationOperationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listLicenseConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listLicenseConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listLicenseSpecificationsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listLicenseSpecificationsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listResourceInventory(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResourceInventoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listUsageForLicenseConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listUsageForLicenseConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateLicenseConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateLicenseConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateLicenseSpecificationsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateLicenseSpecificationsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateServiceSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateServiceSettingsAsync(array $args = [])
 */
class LicenseManagerClient extends AwsClient {}
