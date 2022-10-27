<?php

namespace MediaCloud\Vendor\Aws\S3Control;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CacheInterface;
use MediaCloud\Vendor\Aws\HandlerList;
use MediaCloud\Vendor\Aws\S3\UseArnRegion\Configuration;
use MediaCloud\Vendor\Aws\S3\UseArnRegion\ConfigurationInterface;
use MediaCloud\Vendor\Aws\S3\UseArnRegion\ConfigurationProvider as UseArnRegionConfigurationProvider;
use MediaCloud\Vendor\GuzzleHttp\Promise\PromiseInterface;

/**
 * This client is used to interact with the **AWS S3 Control** service.
 * @method \MediaCloud\Vendor\Aws\Result createAccessPoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAccessPointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createAccessPointForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAccessPointForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createBucket(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createBucketAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createMultiRegionAccessPoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMultiRegionAccessPointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAccessPoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAccessPointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAccessPointForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAccessPointForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAccessPointPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAccessPointPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAccessPointPolicyForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAccessPointPolicyForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucket(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketLifecycleConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketLifecycleConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteJobTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteJobTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteMultiRegionAccessPoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteMultiRegionAccessPointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePublicAccessBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePublicAccessBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteStorageLensConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteStorageLensConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteStorageLensConfigurationTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteStorageLensConfigurationTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeMultiRegionAccessPointOperation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeMultiRegionAccessPointOperationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPointConfigurationForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointConfigurationForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPointForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPointPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPointPolicyForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointPolicyForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPointPolicyStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointPolicyStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccessPointPolicyStatusForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccessPointPolicyStatusForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucket(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketLifecycleConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketLifecycleConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getJobTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getJobTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMultiRegionAccessPoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMultiRegionAccessPointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMultiRegionAccessPointPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMultiRegionAccessPointPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMultiRegionAccessPointPolicyStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMultiRegionAccessPointPolicyStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPublicAccessBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPublicAccessBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getStorageLensConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getStorageLensConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getStorageLensConfigurationTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getStorageLensConfigurationTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAccessPoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAccessPointsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAccessPointsForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAccessPointsForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listMultiRegionAccessPoints(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listMultiRegionAccessPointsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRegionalBuckets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRegionalBucketsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listStorageLensConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listStorageLensConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putAccessPointConfigurationForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putAccessPointConfigurationForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putAccessPointPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putAccessPointPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putAccessPointPolicyForObjectLambda(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putAccessPointPolicyForObjectLambdaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketLifecycleConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketLifecycleConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putJobTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putJobTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putMultiRegionAccessPointPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putMultiRegionAccessPointPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putPublicAccessBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putPublicAccessBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putStorageLensConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putStorageLensConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putStorageLensConfigurationTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putStorageLensConfigurationTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateJobPriority(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateJobPriorityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateJobStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateJobStatusAsync(array $args = [])
 */
class S3ControlClient extends AwsClient 
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        return $args + [
            'use_dual_stack_endpoint' => [
                'type' => 'config',
                'valid' => ['bool'],
                'doc' => 'Set to true to send requests to an S3 Control Dual Stack'
                    . ' endpoint by default, which enables IPv6 Protocol.'
                    . ' Can be enabled or disabled on individual operations by setting'
                    . ' \'@use_dual_stack_endpoint\' to true or false.',
                'default' => false,
            ],
            'use_arn_region' => [
                'type'    => 'config',
                'valid'   => [
                    'bool',
                    Configuration::class,
                    CacheInterface::class,
                    'callable'
                ],
                'doc'     => 'Set to true to allow passed in ARNs to override'
                    . ' client region. Accepts...',
                'fn' => [__CLASS__, '_apply_use_arn_region'],
                'default' => [UseArnRegionConfigurationProvider::class, 'defaultProvider'],
            ],
        ];
    }

    public static function _apply_use_arn_region($value, array &$args, HandlerList $list)
    {
        if ($value instanceof CacheInterface) {
            $value = UseArnRegionConfigurationProvider::defaultProvider($args);
        }
        if (is_callable($value)) {
            $value = $value();
        }
        if ($value instanceof PromiseInterface) {
            $value = $value->wait();
        }
        if ($value instanceof ConfigurationInterface) {
            $args['use_arn_region'] = $value;
        } else {
            // The Configuration class itself will validate other inputs
            $args['use_arn_region'] = new Configuration($value);
        }
    }

    /**
     * {@inheritdoc}
     *
     * In addition to the options available to
     * {@see MediaCloud\Vendor\Aws\AwsClient::__construct}, S3ControlClient accepts the following
     * option:
     *
     * - use_dual_stack_endpoint: (bool) Set to true to send requests to an S3
     *   Control Dual Stack endpoint by default, which enables IPv6 Protocol.
     *   Can be enabled or disabled on individual operations by setting
     *   '@use_dual_stack_endpoint\' to true or false. Note:
     *   you cannot use it together with an accelerate endpoint.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        $stack = $this->getHandlerList();
        $stack->appendBuild(
            EndpointArnMiddleware::wrap(
                $this->getApi(),
                $this->getRegion(),
                [
                    'use_arn_region' => $this->getConfig('use_arn_region'),
                    'dual_stack' =>
                        $this->getConfig('use_dual_stack_endpoint')->isUseDualStackEndpoint(),
                    'endpoint' => isset($args['endpoint'])
                        ? $args['endpoint']
                        : null,
                    'use_fips_endpoint' => $this->getConfig('use_fips_endpoint'),
                ]
            ),
            's3control.endpoint_arn_middleware'
        );
    }
}
