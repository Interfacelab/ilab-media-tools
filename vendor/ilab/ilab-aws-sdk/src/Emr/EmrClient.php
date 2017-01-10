<?php
namespace ILAB_Aws\Emr;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic MapReduce (Amazon EMR)** service.
 *
 * @method \ILAB_Aws\Result addInstanceGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addInstanceGroupsAsync(array $args = [])
 * @method \ILAB_Aws\Result addJobFlowSteps(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addJobFlowStepsAsync(array $args = [])
 * @method \ILAB_Aws\Result addTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeClusterAsync(array $args = [])
 * @method \ILAB_Aws\Result describeJobFlows(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeJobFlowsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeStep(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStepAsync(array $args = [])
 * @method \ILAB_Aws\Result listBootstrapActions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBootstrapActionsAsync(array $args = [])
 * @method \ILAB_Aws\Result listClusters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \ILAB_Aws\Result listInstanceGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInstanceGroupsAsync(array $args = [])
 * @method \ILAB_Aws\Result listInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInstancesAsync(array $args = [])
 * @method \ILAB_Aws\Result listSteps(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStepsAsync(array $args = [])
 * @method \ILAB_Aws\Result modifyInstanceGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyInstanceGroupsAsync(array $args = [])
 * @method \ILAB_Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result runJobFlow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise runJobFlowAsync(array $args = [])
 * @method \ILAB_Aws\Result setTerminationProtection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise setTerminationProtectionAsync(array $args = [])
 * @method \ILAB_Aws\Result setVisibleToAllUsers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise setVisibleToAllUsersAsync(array $args = [])
 * @method \ILAB_Aws\Result terminateJobFlows(array $args = [])
 * @method \GuzzleHttp\Promise\Promise terminateJobFlowsAsync(array $args = [])
 */
class EmrClient extends AwsClient {}
