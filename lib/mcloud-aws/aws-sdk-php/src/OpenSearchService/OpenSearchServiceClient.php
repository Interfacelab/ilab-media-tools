<?php

namespace MediaCloud\Vendor\Aws\OpenSearchService;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon OpenSearch Service** service.
 * @method \MediaCloud\Vendor\Aws\Result acceptInboundConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise acceptInboundConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result addTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result associatePackage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associatePackageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result cancelServiceSoftwareUpdate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelServiceSoftwareUpdateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDomain(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDomainAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createOutboundConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createOutboundConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createPackage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPackageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDomain(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDomainAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteInboundConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteInboundConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteOutboundConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteOutboundConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePackage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePackageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDomain(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDomainAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDomainAutoTunes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDomainAutoTunesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDomainChangeProgress(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDomainChangeProgressAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDomainConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDomainConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDomains(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDomainsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeInboundConnections(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeInboundConnectionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeInstanceTypeLimits(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeInstanceTypeLimitsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOutboundConnections(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOutboundConnectionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePackages(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePackagesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeReservedInstanceOfferings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReservedInstanceOfferingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeReservedInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReservedInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result dissociatePackage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise dissociatePackageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCompatibleVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCompatibleVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPackageVersionHistory(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPackageVersionHistoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getUpgradeHistory(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getUpgradeHistoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getUpgradeStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getUpgradeStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDomainNames(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDomainNamesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDomainsForPackage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDomainsForPackageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listInstanceTypeDetails(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listInstanceTypeDetailsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPackagesForDomain(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPackagesForDomainAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result purchaseReservedInstanceOffering(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise purchaseReservedInstanceOfferingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rejectInboundConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rejectInboundConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startServiceSoftwareUpdate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startServiceSoftwareUpdateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDomainConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDomainConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updatePackage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePackageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result upgradeDomain(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise upgradeDomainAsync(array $args = [])
 */
class OpenSearchServiceClient extends AwsClient {}