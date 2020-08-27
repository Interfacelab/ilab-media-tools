<?php

namespace MediaCloud\Vendor\Aws\Batch;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Batch** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createComputeEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createComputeEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createJobQueue(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createJobQueueAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteComputeEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteComputeEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteJobQueue(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteJobQueueAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deregisterJobDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterJobDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeComputeEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeComputeEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJobDefinitions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJobDefinitionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJobQueues(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJobQueuesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result registerJobDefinition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerJobDefinitionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result submitJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise submitJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result terminateJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise terminateJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateComputeEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateComputeEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateJobQueue(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateJobQueueAsync(array $args = [])
 */
class BatchClient extends AwsClient {}
