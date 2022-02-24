<?php

namespace MediaCloud\Vendor\Aws\PrometheusService;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Prometheus Service** service.
 * @method \MediaCloud\Vendor\Aws\Result createAlertManagerDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAlertManagerDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createRuleGroupsNamespace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createRuleGroupsNamespaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAlertManagerDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAlertManagerDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteRuleGroupsNamespace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteRuleGroupsNamespaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeAlertManagerDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAlertManagerDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRuleGroupsNamespace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRuleGroupsNamespaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRuleGroupsNamespaces(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRuleGroupsNamespacesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listWorkspaces(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listWorkspacesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putAlertManagerDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putAlertManagerDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putRuleGroupsNamespace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putRuleGroupsNamespaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateWorkspaceAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateWorkspaceAliasAsync(array $args = [])
 */
class PrometheusServiceClient extends AwsClient {}
