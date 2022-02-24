<?php

namespace MediaCloud\Vendor\Aws\Detective;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Detective** service.
 * @method \MediaCloud\Vendor\Aws\Result acceptInvitation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise acceptInvitationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createGraph(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGraphAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createMembers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMembersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteGraph(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteGraphAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteMembers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteMembersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOrganizationConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOrganizationConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disableOrganizationAdminAccount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disableOrganizationAdminAccountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateMembership(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateMembershipAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result enableOrganizationAdminAccount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise enableOrganizationAdminAccountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMembers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMembersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGraphs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGraphsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listInvitations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listInvitationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listMembers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listMembersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listOrganizationAdminAccounts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listOrganizationAdminAccountsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rejectInvitation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rejectInvitationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startMonitoringMember(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startMonitoringMemberAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateOrganizationConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateOrganizationConfigurationAsync(array $args = [])
 */
class DetectiveClient extends AwsClient {}
