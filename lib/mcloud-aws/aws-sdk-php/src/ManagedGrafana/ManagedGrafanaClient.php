<?php

namespace MediaCloud\Vendor\Aws\ManagedGrafana;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Managed Grafana** service.
 * @method \MediaCloud\Vendor\Aws\Result associateLicense(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateLicenseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeWorkspaceAuthentication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeWorkspaceAuthenticationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateLicense(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateLicenseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listPermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listWorkspaces(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listWorkspacesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updatePermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updatePermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateWorkspace(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateWorkspaceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateWorkspaceAuthentication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateWorkspaceAuthenticationAsync(array $args = [])
 */
class ManagedGrafanaClient extends AwsClient {}
