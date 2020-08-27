<?php

namespace MediaCloud\Vendor\Aws\MigrationHub;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Migration Hub** service.
 * @method \MediaCloud\Vendor\Aws\Result associateCreatedArtifact(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateCreatedArtifactAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result associateDiscoveredResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateDiscoveredResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createProgressUpdateStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createProgressUpdateStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteProgressUpdateStream(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteProgressUpdateStreamAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeApplicationState(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeApplicationStateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeMigrationTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeMigrationTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateCreatedArtifact(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateCreatedArtifactAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateDiscoveredResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateDiscoveredResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importMigrationTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importMigrationTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listApplicationStates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listApplicationStatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCreatedArtifacts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCreatedArtifactsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDiscoveredResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDiscoveredResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listMigrationTasks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listMigrationTasksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listProgressUpdateStreams(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listProgressUpdateStreamsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result notifyApplicationState(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise notifyApplicationStateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result notifyMigrationTaskState(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise notifyMigrationTaskStateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putResourceAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putResourceAttributesAsync(array $args = [])
 */
class MigrationHubClient extends AwsClient {}
