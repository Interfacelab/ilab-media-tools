<?php

namespace MediaCloud\Vendor\Aws\OpsWorks;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS OpsWorks** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result assignInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise assignInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result assignVolume(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise assignVolumeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result associateElasticIp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateElasticIpAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result attachElasticLoadBalancer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise attachElasticLoadBalancerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result cloneStack(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cloneStackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createApp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAppAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDeployment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDeploymentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createLayer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createLayerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createStack(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createStackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createUserProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createUserProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteApp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAppAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteLayer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteLayerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteStack(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteStackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteUserProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteUserProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterEcsCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterEcsClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterElasticIp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterElasticIpAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterRdsDbInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterRdsDbInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterVolume(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterVolumeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAgentVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAgentVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeApps(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAppsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCommands(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCommandsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDeployments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDeploymentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEcsClusters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEcsClustersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeElasticIps(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeElasticIpsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeElasticLoadBalancers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeElasticLoadBalancersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeLayers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeLayersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeLoadBasedAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeLoadBasedAutoScalingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeMyUserProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeMyUserProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOperatingSystems(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOperatingSystemsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRaidArrays(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRaidArraysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRdsDbInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRdsDbInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeServiceErrors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeServiceErrorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeStackProvisioningParameters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeStackProvisioningParametersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeStackSummary(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeStackSummaryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeStacks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeStacksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTimeBasedAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTimeBasedAutoScalingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeUserProfiles(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeUserProfilesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeVolumes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeVolumesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result detachElasticLoadBalancer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise detachElasticLoadBalancerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateElasticIp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateElasticIpAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getHostnameSuggestion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getHostnameSuggestionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result grantAccess(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise grantAccessAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rebootInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rebootInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerEcsCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerEcsClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerElasticIp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerElasticIpAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerRdsDbInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerRdsDbInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerVolume(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerVolumeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setLoadBasedAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setLoadBasedAutoScalingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setPermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setPermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setTimeBasedAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setTimeBasedAutoScalingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startStack(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startStackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopStack(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopStackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unassignInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unassignInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unassignVolume(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unassignVolumeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateAppAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateElasticIp(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateElasticIpAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateLayer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateLayerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateMyUserProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateMyUserProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateRdsDbInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateRdsDbInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateStack(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateStackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateUserProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateUserProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateVolume(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateVolumeAsync(array $args = [])
 */
class OpsWorksClient extends AwsClient {}
