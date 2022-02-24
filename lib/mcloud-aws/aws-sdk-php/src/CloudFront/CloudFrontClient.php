<?php

namespace MediaCloud\Vendor\Aws\CloudFront;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudFront** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result createCloudFrontOriginAccessIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createInvalidation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createInvalidationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createStreamingDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createStreamingDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCloudFrontOriginAccessIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteStreamingDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteStreamingDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCloudFrontOriginAccessIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCloudFrontOriginAccessIdentityConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCloudFrontOriginAccessIdentityConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDistributionConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDistributionConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getInvalidation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getInvalidationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getStreamingDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getStreamingDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getStreamingDistributionConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getStreamingDistributionConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCloudFrontOriginAccessIdentities(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCloudFrontOriginAccessIdentitiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDistributions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDistributionsByWebACLId(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsByWebACLIdAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listInvalidations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listInvalidationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listStreamingDistributions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listStreamingDistributionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateCloudFrontOriginAccessIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateStreamingDistribution(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateStreamingDistributionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDistributionWithTags(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDistributionWithTagsAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createStreamingDistributionWithTags(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createStreamingDistributionWithTagsAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteServiceLinkedRole(array $args = []) (supported in versions 2017-03-25)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteServiceLinkedRoleAsync(array $args = []) (supported in versions 2017-03-25)
 * @method \MediaCloud\Vendor\Aws\Result createFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createPublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createPublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deletePublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getFieldLevelEncryption(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFieldLevelEncryptionAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getFieldLevelEncryptionProfileConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFieldLevelEncryptionProfileConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getPublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getPublicKeyConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPublicKeyConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listFieldLevelEncryptionConfigs(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFieldLevelEncryptionConfigsAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listFieldLevelEncryptionProfiles(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFieldLevelEncryptionProfilesAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listPublicKeys(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPublicKeysAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updatePublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result associateAlias(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateAliasAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createMonitoringSubscription(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMonitoringSubscriptionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result createResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteMonitoringSubscription(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteMonitoringSubscriptionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result describeFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getCachePolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCachePolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getKeyGroupConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getKeyGroupConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getMonitoringSubscription(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMonitoringSubscriptionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getOriginRequestPolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getOriginRequestPolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result getResponseHeadersPolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResponseHeadersPolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listCachePolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCachePoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listConflictingAliases(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listConflictingAliasesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listDistributionsByCachePolicyId(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsByCachePolicyIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listDistributionsByKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsByKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listDistributionsByOriginRequestPolicyId(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsByOriginRequestPolicyIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listDistributionsByRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsByRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listDistributionsByResponseHeadersPolicyId(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDistributionsByResponseHeadersPolicyIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listFunctions(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFunctionsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listKeyGroups(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listKeyGroupsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listOriginRequestPolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listOriginRequestPoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listRealtimeLogConfigs(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRealtimeLogConfigsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result listResponseHeadersPolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResponseHeadersPoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result publishFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result testFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise testFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\Aws\Result updateResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 */
class CloudFrontClient extends AwsClient
{
    /**
     * Create a signed Amazon CloudFront URL.
     *
     * This method accepts an array of configuration options:
     *
     * - url: (string)  URL of the resource being signed (can include query
     *   string and wildcards). For example: rtmp://s5c39gqb8ow64r.cloudfront.net/videos/mp3_name.mp3
     *   http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * - policy: (string) JSON policy. Use this option when creating a signed
     *   URL for a custom policy.
     * - expires: (int) UTC Unix timestamp used when signing with a canned
     *   policy. Not required when passing a custom 'policy' option.
     * - key_pair_id: (string) The ID of the key pair used to sign CloudFront
     *   URLs for private distributions.
     * - private_key: (string) The filepath ot the private key used to sign
     *   CloudFront URLs for private distributions.
     *
     * @param array $options Array of configuration options used when signing
     *
     * @return string Signed URL with authentication parameters
     * @throws \InvalidArgumentException if url, key_pair_id, or private_key
     *     were not specified.
     * @link http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/WorkingWithStreamingDistributions.html
     */
    public function getSignedUrl(array $options)
    {
        foreach (['url', 'key_pair_id', 'private_key'] as $required) {
            if (!isset($options[$required])) {
                throw new \InvalidArgumentException("$required is required");
            }
        }

        $urlSigner = new UrlSigner(
            $options['key_pair_id'],
            $options['private_key']
        );

        return $urlSigner->getSignedUrl(
            $options['url'],
            isset($options['expires']) ? $options['expires'] : null,
            isset($options['policy']) ? $options['policy'] : null
        );
    }

    /**
     * Create a signed Amazon CloudFront cookie.
     *
     * This method accepts an array of configuration options:
     *
     * - url: (string)  URL of the resource being signed (can include query
     *   string and wildcards). For example: http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * - policy: (string) JSON policy. Use this option when creating a signed
     *   URL for a custom policy.
     * - expires: (int) UTC Unix timestamp used when signing with a canned
     *   policy. Not required when passing a custom 'policy' option.
     * - key_pair_id: (string) The ID of the key pair used to sign CloudFront
     *   URLs for private distributions.
     * - private_key: (string) The filepath ot the private key used to sign
     *   CloudFront URLs for private distributions.
     *
     * @param array $options Array of configuration options used when signing
     *
     * @return array Key => value pairs of signed cookies to set
     * @throws \InvalidArgumentException if url, key_pair_id, or private_key
     *     were not specified.
     * @link http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/WorkingWithStreamingDistributions.html
     */
    public function getSignedCookie(array $options)
    {
        foreach (['key_pair_id', 'private_key'] as $required) {
            if (!isset($options[$required])) {
                throw new \InvalidArgumentException("$required is required");
            }
        }

        $cookieSigner = new CookieSigner(
            $options['key_pair_id'],
            $options['private_key']
        );

        return $cookieSigner->getSignedCookie(
            isset($options['url']) ? $options['url'] : null,
            isset($options['expires']) ? $options['expires'] : null,
            isset($options['policy']) ? $options['policy'] : null
        );
    }
}
