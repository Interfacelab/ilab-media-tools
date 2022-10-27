<?php

namespace MediaCloud\Vendor\Aws\ResourceGroups;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Resource Groups** service.
 * @method \MediaCloud\Vendor\Aws\Result createGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getGroupConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getGroupConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getGroupQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getGroupQueryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result groupResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise groupResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGroupResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGroupResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putGroupConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putGroupConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result searchResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise searchResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tag(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result ungroupResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise ungroupResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untag(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateGroupQuery(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateGroupQueryAsync(array $args = [])
 */
class ResourceGroupsClient extends AwsClient {}
