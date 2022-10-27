<?php

namespace MediaCloud\Vendor\Aws\EMRContainers;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon EMR Containers** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelJobRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelJobRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createManagedEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createManagedEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createVirtualCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createVirtualClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteManagedEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteManagedEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteVirtualCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteVirtualClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJobRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJobRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeManagedEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeManagedEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeVirtualCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeVirtualClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJobRuns(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJobRunsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listManagedEndpoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listManagedEndpointsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listVirtualClusters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listVirtualClustersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startJobRun(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startJobRunAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class EMRContainersClient extends AwsClient {}
