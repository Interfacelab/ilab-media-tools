<?php

namespace MediaCloud\Vendor\Aws\Route53;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * This client is used to interact with the **Amazon Route 53** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result activateKeySigningKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise activateKeySigningKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result associateVPCWithHostedZone(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateVPCWithHostedZoneAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result changeResourceRecordSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise changeResourceRecordSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result changeTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise changeTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHealthCheck(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHealthCheckAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHostedZone(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHostedZoneAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createKeySigningKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createKeySigningKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createQueryLoggingConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createQueryLoggingConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createReusableDelegationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createReusableDelegationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTrafficPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTrafficPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTrafficPolicyInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTrafficPolicyInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTrafficPolicyVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTrafficPolicyVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createVPCAssociationAuthorization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createVPCAssociationAuthorizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deactivateKeySigningKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deactivateKeySigningKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHealthCheck(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHealthCheckAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHostedZone(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHostedZoneAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteKeySigningKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteKeySigningKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteQueryLoggingConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteQueryLoggingConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteReusableDelegationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteReusableDelegationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTrafficPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTrafficPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTrafficPolicyInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTrafficPolicyInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteVPCAssociationAuthorization(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteVPCAssociationAuthorizationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disableHostedZoneDNSSEC(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disableHostedZoneDNSSECAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateVPCFromHostedZone(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateVPCFromHostedZoneAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result enableHostedZoneDNSSEC(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise enableHostedZoneDNSSECAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccountLimit(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccountLimitAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getChange(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getChangeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCheckerIpRanges(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCheckerIpRangesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDNSSEC(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDNSSECAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getGeoLocation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getGeoLocationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHealthCheck(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHealthCheckAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHealthCheckCount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHealthCheckCountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHealthCheckLastFailureReason(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHealthCheckLastFailureReasonAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHealthCheckStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHealthCheckStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHostedZone(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHostedZoneAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHostedZoneCount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHostedZoneCountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHostedZoneLimit(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHostedZoneLimitAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getQueryLoggingConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getQueryLoggingConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getReusableDelegationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getReusableDelegationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getReusableDelegationSetLimit(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getReusableDelegationSetLimitAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTrafficPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTrafficPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTrafficPolicyInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTrafficPolicyInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTrafficPolicyInstanceCount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTrafficPolicyInstanceCountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGeoLocations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGeoLocationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHealthChecks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHealthChecksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHostedZones(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHostedZonesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHostedZonesByName(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHostedZonesByNameAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHostedZonesByVPC(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHostedZonesByVPCAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listQueryLoggingConfigs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listQueryLoggingConfigsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listResourceRecordSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResourceRecordSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listReusableDelegationSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listReusableDelegationSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTrafficPolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTrafficPoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTrafficPolicyInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTrafficPolicyInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTrafficPolicyInstancesByHostedZone(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTrafficPolicyInstancesByHostedZoneAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTrafficPolicyInstancesByPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTrafficPolicyInstancesByPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTrafficPolicyVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTrafficPolicyVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listVPCAssociationAuthorizations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listVPCAssociationAuthorizationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result testDNSAnswer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise testDNSAnswerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateHealthCheck(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateHealthCheckAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateHostedZoneComment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateHostedZoneCommentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTrafficPolicyComment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTrafficPolicyCommentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTrafficPolicyInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTrafficPolicyInstanceAsync(array $args = [])
 */
class Route53Client extends AwsClient
{
    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->getHandlerList()->appendInit($this->cleanIdFn(), 'route53.clean_id');
    }

    private function cleanIdFn()
    {
        return function (callable $handler) {
            return function (CommandInterface $c, RequestInterface $r = null) use ($handler) {
                foreach (['Id', 'HostedZoneId', 'DelegationSetId'] as $clean) {
                    if ($c->hasParam($clean)) {
                        $c[$clean] = $this->cleanId($c[$clean]);
                    }
                }
                return $handler($c, $r);
            };
        };
    }

    private function cleanId($id)
    {
        static $toClean = ['/hostedzone/', '/change/', '/delegationset/'];

        return str_replace($toClean, '', $id);
    }
}
