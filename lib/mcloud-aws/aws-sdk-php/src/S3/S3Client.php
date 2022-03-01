<?php

namespace MediaCloud\Vendor\Aws\S3;
use MediaCloud\Vendor\Aws\Api\ApiProvider;
use MediaCloud\Vendor\Aws\Api\DocModel;
use MediaCloud\Vendor\Aws\Api\Service;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CacheInterface;
use MediaCloud\Vendor\Aws\ClientResolver;
use MediaCloud\Vendor\Aws\Command;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\HandlerList;
use MediaCloud\Vendor\Aws\InputValidationMiddleware;
use MediaCloud\Vendor\Aws\Middleware;
use MediaCloud\Vendor\Aws\Retry\QuotaManager;
use MediaCloud\Vendor\Aws\RetryMiddleware;
use MediaCloud\Vendor\Aws\ResultInterface;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Aws\RetryMiddlewareV2;
use MediaCloud\Vendor\Aws\S3\UseArnRegion\Configuration;
use MediaCloud\Vendor\Aws\S3\UseArnRegion\ConfigurationInterface;
use MediaCloud\Vendor\Aws\S3\UseArnRegion\ConfigurationProvider as UseArnRegionConfigurationProvider;
use MediaCloud\Vendor\Aws\S3\RegionalEndpoint\ConfigurationProvider;
use MediaCloud\Vendor\GuzzleHttp\Exception\RequestException;
use MediaCloud\Vendor\GuzzleHttp\Promise\Promise;
use MediaCloud\Vendor\GuzzleHttp\Promise\PromiseInterface;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * Client used to interact with **Amazon Simple Storage Service (Amazon S3)**.
 *
 * @method \MediaCloud\Vendor\Aws\Result abortMultipartUpload(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise abortMultipartUploadAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result completeMultipartUpload(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise completeMultipartUploadAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result copyObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createBucket(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createBucketAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createMultipartUpload(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMultipartUploadAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucket(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketAnalyticsConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketCors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketCorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketEncryption(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketEncryptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketIntelligentTieringConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketIntelligentTieringConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketInventoryConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketInventoryConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketLifecycle(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketLifecycleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketMetricsConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketMetricsConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketOwnershipControls(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketOwnershipControlsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketReplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketReplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBucketWebsite(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBucketWebsiteAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteObjectTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteObjectTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteObjects(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteObjectsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deletePublicAccessBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deletePublicAccessBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketAccelerateConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketAccelerateConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketAcl(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketAclAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketAnalyticsConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketCors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketCorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketEncryption(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketEncryptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketIntelligentTieringConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketIntelligentTieringConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketInventoryConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketInventoryConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketLifecycle(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketLifecycleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketLifecycleConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketLifecycleConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketLocation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketLocationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketLogging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketLoggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketMetricsConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketMetricsConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketNotification(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketNotificationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketNotificationConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketNotificationConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketOwnershipControls(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketOwnershipControlsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketPolicyStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketPolicyStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketReplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketReplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketRequestPayment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketRequestPaymentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketVersioning(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketVersioningAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBucketWebsite(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBucketWebsiteAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectAcl(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectAclAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectLegalHold(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectLegalHoldAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectLockConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectLockConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectRetention(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectRetentionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObjectTorrent(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectTorrentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPublicAccessBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPublicAccessBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result headBucket(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise headBucketAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result headObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise headObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listBucketAnalyticsConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBucketAnalyticsConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listBucketIntelligentTieringConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBucketIntelligentTieringConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listBucketInventoryConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBucketInventoryConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listBucketMetricsConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBucketMetricsConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listBuckets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBucketsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listMultipartUploads(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listMultipartUploadsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listObjectVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listObjectVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listObjects(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listObjectsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listObjectsV2(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listObjectsV2Async(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listParts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listPartsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketAccelerateConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketAccelerateConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketAcl(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketAclAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketAnalyticsConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketCors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketCorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketEncryption(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketEncryptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketIntelligentTieringConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketIntelligentTieringConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketInventoryConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketInventoryConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketLifecycle(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketLifecycleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketLifecycleConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketLifecycleConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketLogging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketLoggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketMetricsConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketMetricsConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketNotification(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketNotificationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketNotificationConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketNotificationConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketOwnershipControls(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketOwnershipControlsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketReplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketReplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketRequestPayment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketRequestPaymentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketVersioning(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketVersioningAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putBucketWebsite(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putBucketWebsiteAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObjectAcl(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectAclAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObjectLegalHold(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectLegalHoldAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObjectLockConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectLockConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObjectRetention(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectRetentionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObjectTagging(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectTaggingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putPublicAccessBlock(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putPublicAccessBlockAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result selectObjectContent(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise selectObjectContentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result uploadPart(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise uploadPartAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result uploadPartCopy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise uploadPartCopyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result writeGetObjectResponse(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise writeGetObjectResponseAsync(array $args = [])
 */
class S3Client extends AwsClient implements S3ClientInterface
{
    use S3ClientTrait;

    /** @var array */
    private static $mandatoryAttributes = ['Bucket', 'Key'];

    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['fn'] = [__CLASS__, '_applyRetryConfig'];
        $args['api_provider']['fn'] = [__CLASS__, '_applyApiProvider'];

        return $args + [
            'bucket_endpoint' => [
                'type'    => 'config',
                'valid'   => ['bool'],
                'doc'     => 'Set to true to send requests to a hardcoded '
                    . 'bucket endpoint rather than create an endpoint as a '
                    . 'result of injecting the bucket into the URL. This '
                    . 'option is useful for interacting with CNAME endpoints.',
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
            'use_accelerate_endpoint' => [
                'type' => 'config',
                'valid' => ['bool'],
                'doc' => 'Set to true to send requests to an S3 Accelerate'
                    . ' endpoint by default. Can be enabled or disabled on'
                    . ' individual operations by setting'
                    . ' \'@use_accelerate_endpoint\' to true or false. Note:'
                    . ' you must enable S3 Accelerate on a bucket before it can'
                    . ' be accessed via an Accelerate endpoint.',
                'default' => false,
            ],
            'use_path_style_endpoint' => [
                'type' => 'config',
                'valid' => ['bool'],
                'doc' => 'Set to true to send requests to an S3 path style'
                    . ' endpoint by default.'
                    . ' Can be enabled or disabled on individual operations by setting'
                    . ' \'@use_path_style_endpoint\' to true or false.',
                'default' => false,
            ],
            'disable_multiregion_access_points' => [
                'type' => 'config',
                'valid' => ['bool'],
                'doc' => 'Set to true to disable the usage of'
                    . ' multi region access points. These are enabled by default.'
                    . ' Can be enabled or disabled on individual operations by setting'
                    . ' \'@disable_multiregion_access_points\' to true or false.',
                'default' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * In addition to the options available to
     * {@see MediaCloud\Vendor\Aws\AwsClient::__construct}, S3Client accepts the following
     * options:
     *
     * - bucket_endpoint: (bool) Set to true to send requests to a
     *   hardcoded bucket endpoint rather than create an endpoint as a result
     *   of injecting the bucket into the URL. This option is useful for
     *   interacting with CNAME endpoints.
     * - calculate_md5: (bool) Set to false to disable calculating an MD5
     *   for all Amazon S3 signed uploads.
     * - s3_us_east_1_regional_endpoint:
     *   (Aws\S3\RegionalEndpoint\ConfigurationInterface|Aws\CacheInterface\|callable|string|array)
     *   Specifies whether to use regional or legacy endpoints for the us-east-1
     *   region. Provide an MediaCloud\Vendor\Aws\S3\RegionalEndpoint\ConfigurationInterface object, an
     *   instance of MediaCloud\Vendor\Aws\CacheInterface, a callable configuration provider used
     *   to create endpoint configuration, a string value of `legacy` or
     *   `regional`, or an associative array with the following keys:
     *   endpoint_types: (string)  Set to `legacy` or `regional`, defaults to
     *   `legacy`
     * - use_accelerate_endpoint: (bool) Set to true to send requests to an S3
     *   Accelerate endpoint by default. Can be enabled or disabled on
     *   individual operations by setting '@use_accelerate_endpoint' to true or
     *   false. Note: you must enable S3 Accelerate on a bucket before it can be
     *   accessed via an Accelerate endpoint.
     * - use_arn_region: (Aws\S3\UseArnRegion\ConfigurationInterface,
     * MediaCloud\Vendor\Aws\CacheInterface, bool, callable) Set to true to enable the client
     *   to use the region from a supplied ARN argument instead of the client's
     *   region. Provide an instance of MediaCloud\Vendor\Aws\S3\UseArnRegion\ConfigurationInterface,
     *   an instance of MediaCloud\Vendor\Aws\CacheInterface, a callable that provides a promise for
     *   a Configuration object, or a boolean value. Defaults to false (i.e.
     *   the SDK will not follow the ARN region if it conflicts with the client
     *   region and instead throw an error).
     * - use_dual_stack_endpoint: (bool) Set to true to send requests to an S3
     *   Dual Stack endpoint by default, which enables IPv6 Protocol.
     *   Can be enabled or disabled on individual operations by setting
     *   '@use_dual_stack_endpoint\' to true or false. Note:
     *   you cannot use it together with an accelerate endpoint.
     * - use_path_style_endpoint: (bool) Set to true to send requests to an S3
     *   path style endpoint by default.
     *   Can be enabled or disabled on individual operations by setting
     *   '@use_path_style_endpoint\' to true or false. Note:
     *   you cannot use it together with an accelerate endpoint.
     * - disable_multiregion_access_points: (bool) Set to true to disable
     *   sending multi region requests.  They are enabled by default.
     *   Can be enabled or disabled on individual operations by setting
     *   '@disable_multiregion_access_points\' to true or false. Note:
     *   you cannot use it together with an accelerate or dualstack endpoint.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        if (
            !isset($args['s3_us_east_1_regional_endpoint'])
            || $args['s3_us_east_1_regional_endpoint'] instanceof CacheInterface
        ) {
            $args['s3_us_east_1_regional_endpoint'] = ConfigurationProvider::defaultProvider($args);
        }
        parent::__construct($args);
        $stack = $this->getHandlerList();
        $stack->appendInit(SSECMiddleware::wrap($this->getEndpoint()->getScheme()), 's3.ssec');
        $stack->appendBuild(ApplyChecksumMiddleware::wrap($this->getApi()), 's3.checksum');
        $stack->appendBuild(
            Middleware::contentType(['PutObject', 'UploadPart']),
            's3.content_type'
        );

        // Use the bucket style middleware when using a "bucket_endpoint" (for cnames)
        if ($this->getConfig('bucket_endpoint')) {
            $stack->appendBuild(BucketEndpointMiddleware::wrap(), 's3.bucket_endpoint');
        } else {
            $stack->appendBuild(
                S3EndpointMiddleware::wrap(
                    $this->getRegion(),
                    $this->getConfig('endpoint_provider'),
                    [
                        'accelerate' => $this->getConfig('use_accelerate_endpoint'),
                        'path_style' => $this->getConfig('use_path_style_endpoint'),
                        'use_fips_endpoint' => $this->getConfig('use_fips_endpoint'),
                        'dual_stack' =>
                            $this->getConfig('use_dual_stack_endpoint')->isUseDualStackEndpoint(),

                    ]
                ),
                's3.endpoint_middleware'
            );
        }

        $stack->appendBuild(
            BucketEndpointArnMiddleware::wrap(
                $this->getApi(),
                $this->getRegion(),
                [
                    'use_arn_region' => $this->getConfig('use_arn_region'),
                    'accelerate' => $this->getConfig('use_accelerate_endpoint'),
                    'path_style' => $this->getConfig('use_path_style_endpoint'),
                    'dual_stack' =>
                        $this->getConfig('use_dual_stack_endpoint')->isUseDualStackEndpoint(),
                    'use_fips_endpoint' => $this->getConfig('use_fips_endpoint'),
                    'disable_multiregion_access_points' =>
                        $this->getConfig('disable_multiregion_access_points'),
                    'endpoint' => isset($args['endpoint'])
                        ? $args['endpoint']
                        : null
                ]
            ),
            's3.bucket_endpoint_arn'
        );

        $stack->appendValidate(
            InputValidationMiddleware::wrap($this->getApi(), self::$mandatoryAttributes),
            'input_validation_middleware'
        );
        $stack->appendSign(PutObjectUrlMiddleware::wrap(), 's3.put_object_url');
        $stack->appendSign(PermanentRedirectMiddleware::wrap(), 's3.permanent_redirect');
        $stack->appendInit(Middleware::sourceFile($this->getApi()), 's3.source_file');
        $stack->appendInit($this->getSaveAsParameter(), 's3.save_as');
        $stack->appendInit($this->getLocationConstraintMiddleware(), 's3.location');
        $stack->appendInit($this->getEncodingTypeMiddleware(), 's3.auto_encode');
        $stack->appendInit($this->getHeadObjectMiddleware(), 's3.head_object');
    }

    /**
     * Determine if a string is a valid name for a DNS compatible Amazon S3
     * bucket.
     *
     * DNS compatible bucket names can be used as a subdomain in a URL (e.g.,
     * "<bucket>.s3.amazonaws.com").
     *
     * @param string $bucket Bucket name to check.
     *
     * @return bool
     */
    public static function isBucketDnsCompatible($bucket)
    {
        $bucketLen = strlen($bucket);

        return ($bucketLen >= 3 && $bucketLen <= 63) &&
            // Cannot look like an IP address
            !filter_var($bucket, FILTER_VALIDATE_IP) &&
            preg_match('/^[a-z0-9]([a-z0-9\-\.]*[a-z0-9])?$/', $bucket);
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

    public function createPresignedRequest(CommandInterface $command, $expires, array $options = [])
    {
        $command = clone $command;
        $command->getHandlerList()->remove('signer');

        /** @var \MediaCloud\Vendor\Aws\Signature\SignatureInterface $signer */
        $signer = call_user_func(
            $this->getSignatureProvider(),
            $this->getConfig('signature_version'),
            $this->getConfig('signing_name'),
            $this->getConfig('signing_region')
        );

        return $signer->presign(\MediaCloud\Vendor\Aws\serialize($command),
            $this->getCredentials()->wait(),
            $expires,
            $options
        );
    }

    /**
     * Returns the URL to an object identified by its bucket and key.
     *
     * The URL returned by this method is not signed nor does it ensure that the
     * bucket and key given to the method exist. If you need a signed URL, then
     * use the {@see \MediaCloud\Vendor\Aws\S3\S3Client::createPresignedRequest} method and get
     * the URI of the signed request.
     *
     * @param string $bucket  The name of the bucket where the object is located
     * @param string $key     The key of the object
     *
     * @return string The URL to the object
     */
    public function getObjectUrl($bucket, $key)
    {
        $command = $this->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $key
        ]);

        return (string) \MediaCloud\Vendor\Aws\serialize($command)->getUri();
    }

    /**
     * Raw URL encode a key and allow for '/' characters
     *
     * @param string $key Key to encode
     *
     * @return string Returns the encoded key
     */
    public static function encodeKey($key)
    {
        return str_replace('%2F', '/', rawurlencode($key));
    }

    /**
     * Provides a middleware that removes the need to specify LocationConstraint on CreateBucket.
     *
     * @return \Closure
     */
    private function getLocationConstraintMiddleware()
    {
        $region = $this->getRegion();
        return static function (callable $handler) use ($region) {
            return function (Command $command, $request = null) use ($handler, $region) {
                if ($command->getName() === 'CreateBucket') {
                    $locationConstraint = isset($command['CreateBucketConfiguration']['LocationConstraint'])
                        ? $command['CreateBucketConfiguration']['LocationConstraint']
                        : null;

                    if ($locationConstraint === 'us-east-1') {
                        unset($command['CreateBucketConfiguration']);
                    } elseif ('us-east-1' !== $region && empty($locationConstraint)) {
                        $command['CreateBucketConfiguration'] = ['LocationConstraint' => $region];
                    }
                }

                return $handler($command, $request);
            };
        };
    }

    /**
     * Provides a middleware that supports the `SaveAs` parameter.
     *
     * @return \Closure
     */
    private function getSaveAsParameter()
    {
        return static function (callable $handler) {
            return function (Command $command, $request = null) use ($handler) {
                if ($command->getName() === 'GetObject' && isset($command['SaveAs'])) {
                    $command['@http']['sink'] = $command['SaveAs'];
                    unset($command['SaveAs']);
                }

                return $handler($command, $request);
            };
        };
    }

    /**
     * Provides a middleware that disables content decoding on HeadObject
     * commands.
     *
     * @return \Closure
     */
    private function getHeadObjectMiddleware()
    {
        return static function (callable $handler) {
            return function (
                CommandInterface $command,
                RequestInterface $request = null
            ) use ($handler) {
                if ($command->getName() === 'HeadObject'
                    && !isset($command['@http']['decode_content'])
                ) {
                    $command['@http']['decode_content'] = false;
                }

                return $handler($command, $request);
            };
        };
    }

    /**
     * Provides a middleware that autopopulates the EncodingType parameter on
     * ListObjects commands.
     *
     * @return \Closure
     */
    private function getEncodingTypeMiddleware()
    {
        return static function (callable $handler) {
            return function (Command $command, $request = null) use ($handler) {
                $autoSet = false;
                if ($command->getName() === 'ListObjects'
                    && empty($command['EncodingType'])
                ) {
                    $command['EncodingType'] = 'url';
                    $autoSet = true;
                }

                return $handler($command, $request)
                    ->then(function (ResultInterface $result) use ($autoSet) {
                        if ($result['EncodingType'] === 'url' && $autoSet) {
                            static $topLevel = [
                                'Delimiter',
                                'Marker',
                                'NextMarker',
                                'Prefix',
                            ];
                            static $nested = [
                                ['Contents', 'Key'],
                                ['CommonPrefixes', 'Prefix'],
                            ];

                            foreach ($topLevel as $key) {
                                if (isset($result[$key])) {
                                    $result[$key] = urldecode($result[$key]);
                                }
                            }
                            foreach ($nested as $steps) {
                                if (isset($result[$steps[0]])) {
                                    foreach ($result[$steps[0]] as $key => $part) {
                                        if (isset($part[$steps[1]])) {
                                            $result[$steps[0]][$key][$steps[1]]
                                                = urldecode($part[$steps[1]]);
                                        }
                                    }
                                }
                            }

                        }

                        return $result;
                    });
            };
        };
    }

    /** @internal */
    public static function _applyRetryConfig($value, $args, HandlerList $list)
    {
        if ($value) {
            $config = \MediaCloud\Vendor\Aws\Retry\ConfigurationProvider::unwrap($value);

            if ($config->getMode() === 'legacy') {
                $maxRetries = $config->getMaxAttempts() - 1;
                $decider = RetryMiddleware::createDefaultDecider($maxRetries);
                $decider = function ($retries, $command, $request, $result, $error) use ($decider, $maxRetries) {
                    $maxRetries = null !== $command['@retries']
                        ? $command['@retries']
                        : $maxRetries;

                    if ($decider($retries, $command, $request, $result, $error)) {
                        return true;
                    }

                    if ($error instanceof AwsException
                        && $retries < $maxRetries
                    ) {
                        if ($error->getResponse()
                            && $error->getResponse()->getStatusCode() >= 400
                        ) {
                            return strpos(
                                    $error->getResponse()->getBody(),
                                    'Your socket connection to the server'
                                ) !== false;
                        }

                        if ($error->getPrevious() instanceof RequestException) {
                            // All commands except CompleteMultipartUpload are
                            // idempotent and may be retried without worry if a
                            // networking error has occurred.
                            return $command->getName() !== 'CompleteMultipartUpload';
                        }
                    }

                    return false;
                };

                $delay = [RetryMiddleware::class, 'exponentialDelay'];
                $list->appendSign(Middleware::retry($decider, $delay), 'retry');
            } else {
                $defaultDecider = RetryMiddlewareV2::createDefaultDecider(
                    new QuotaManager(),
                    $config->getMaxAttempts()
                );

                $list->appendSign(
                    RetryMiddlewareV2::wrap(
                        $config,
                        [
                            'collect_stats' => $args['stats']['retries'],
                            'decider' => function(
                                $attempts,
                                CommandInterface $cmd,
                                $result
                            ) use ($defaultDecider, $config) {
                                $isRetryable = $defaultDecider($attempts, $cmd, $result);
                                if (!$isRetryable
                                    && $result instanceof AwsException
                                    && $attempts < $config->getMaxAttempts()
                                ) {
                                    if (!empty($result->getResponse())
                                        && strpos(
                                            $result->getResponse()->getBody(),
                                            'Your socket connection to the server'
                                        ) !== false
                                    ) {
                                        $isRetryable = false;
                                    }
                                    if ($result->getPrevious() instanceof RequestException
                                        && $cmd->getName() !== 'CompleteMultipartUpload'
                                    ) {
                                        $isRetryable = true;
                                    }
                                }
                                return $isRetryable;
                            }
                        ]
                    ),
                    'retry'
                );
            }
        }
    }

    /** @internal */
    public static function _applyApiProvider($value, array &$args, HandlerList $list)
    {
        ClientResolver::_apply_api_provider($value, $args);
        $args['parser'] = new GetBucketLocationParser(
            new ValidateResponseChecksumParser(
                new AmbiguousSuccessParser(
                    new RetryableMalformedResponseParser(
                        $args['parser'],
                        $args['exception_class']
                    ),
                    $args['error_parser'],
                    $args['exception_class']
                ),
                $args['api']
            )
        );
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        $b64 = '<div class="alert alert-info">This value will be base64 encoded on your behalf.</div>';
        $opt = '<div class="alert alert-info">This value will be computed for you it is not supplied.</div>';

        // Add a note on the CopyObject docs
         $s3ExceptionRetryMessage = "<p>Additional info on response behavior: if there is"
            . " an internal error in S3 after the request was successfully recieved,"
            . " a 200 response will be returned with an <code>S3Exception</code> embedded"
            . " in it; this will still be caught and retried by"
            . " <code>RetryMiddleware.</code></p>";

        $docs['operations']['CopyObject'] .=  $s3ExceptionRetryMessage;
        $docs['operations']['CompleteMultipartUpload'] .=  $s3ExceptionRetryMessage;
        $docs['operations']['UploadPartCopy'] .=  $s3ExceptionRetryMessage;
        $docs['operations']['UploadPart'] .=  $s3ExceptionRetryMessage;

        // Add note about stream ownership in the putObject call
        $guzzleStreamMessage = "<p>Additional info on behavior of the stream"
            . " parameters: Psr7 takes ownership of streams and will automatically close"
            . " streams when this method is called with a stream as the <code>Body</code>"
            . " parameter.  To prevent this, set the <code>Body</code> using"
            . " <code>GuzzleHttp\Psr7\stream_for</code> method with a is an instance of"
            . " <code>Psr\Http\Message\StreamInterface</code>, and it will be returned"
            . " unmodified. This will allow you to keep the stream in scope. </p>";
        $docs['operations']['PutObject'] .=  $guzzleStreamMessage;

        // Add the SourceFile parameter.
        $docs['shapes']['SourceFile']['base'] = 'The path to a file on disk to use instead of the Body parameter.';
        $api['shapes']['SourceFile'] = ['type' => 'string'];
        $api['shapes']['PutObjectRequest']['members']['SourceFile'] = ['shape' => 'SourceFile'];
        $api['shapes']['UploadPartRequest']['members']['SourceFile'] = ['shape' => 'SourceFile'];

        // Add the ContentSHA256 parameter.
        $docs['shapes']['ContentSHA256']['base'] = 'A SHA256 hash of the body content of the request.';
        $api['shapes']['ContentSHA256'] = ['type' => 'string'];
        $api['shapes']['PutObjectRequest']['members']['ContentSHA256'] = ['shape' => 'ContentSHA256'];
        $api['shapes']['UploadPartRequest']['members']['ContentSHA256'] = ['shape' => 'ContentSHA256'];
        unset($api['shapes']['PutObjectRequest']['members']['ContentMD5']);
        unset($api['shapes']['UploadPartRequest']['members']['ContentMD5']);
        $docs['shapes']['ContentSHA256']['append'] = $opt;

        // Add the SaveAs parameter.
        $docs['shapes']['SaveAs']['base'] = 'The path to a file on disk to save the object data.';
        $api['shapes']['SaveAs'] = ['type' => 'string'];
        $api['shapes']['GetObjectRequest']['members']['SaveAs'] = ['shape' => 'SaveAs'];

        // Several SSECustomerKey documentation updates.
        $docs['shapes']['SSECustomerKey']['append'] = $b64;
        $docs['shapes']['CopySourceSSECustomerKey']['append'] = $b64;
        $docs['shapes']['SSECustomerKeyMd5']['append'] = $opt;

        // Add the ObjectURL to various output shapes and documentation.
        $docs['shapes']['ObjectURL']['base'] = 'The URI of the created object.';
        $api['shapes']['ObjectURL'] = ['type' => 'string'];
        $api['shapes']['PutObjectOutput']['members']['ObjectURL'] = ['shape' => 'ObjectURL'];
        $api['shapes']['CopyObjectOutput']['members']['ObjectURL'] = ['shape' => 'ObjectURL'];
        $api['shapes']['CompleteMultipartUploadOutput']['members']['ObjectURL'] = ['shape' => 'ObjectURL'];

        // Fix references to Location Constraint.
        unset($api['shapes']['CreateBucketRequest']['payload']);
        $api['shapes']['BucketLocationConstraint']['enum'] = [
            "ap-northeast-1",
            "ap-southeast-2",
            "ap-southeast-1",
            "cn-north-1",
            "eu-central-1",
            "eu-west-1",
            "us-east-1",
            "us-west-1",
            "us-west-2",
            "sa-east-1",
        ];

        // Add a note that the ContentMD5 is optional.
        $docs['shapes']['ContentMD5']['append'] = '<div class="alert alert-info">The value will be computed on '
            . 'your behalf.</div>';

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function addDocExamples($examples)
    {
        $getObjectExample = [
            'input' => [
                'Bucket' => 'arn:aws:s3:us-east-1:123456789012:accesspoint:myaccesspoint',
                'Key' => 'my-key'
            ],
            'output' => [
                'Body' => 'class \MediaCloud\Vendor\GuzzleHttp\Psr7\Stream#208 (7) {...}',
                'ContentLength' => '11',
                'ContentType' => 'application/octet-stream',
            ],
            'comments' => [
                'input' => '',
                'output' => 'Simplified example output'
            ],
            'description' => 'The following example retrieves an object by referencing the bucket via an S3 accesss point ARN. Result output is simplified for the example.',
            'id' => '',
            'title' => 'To get an object via an S3 access point ARN'
        ];
        if (isset($examples['GetObject'])) {
            $examples['GetObject'] []= $getObjectExample;
        } else {
            $examples['GetObject'] = [$getObjectExample];
        }

        $putObjectExample = [
            'input' => [
                'Bucket' => 'arn:aws:s3:us-east-1:123456789012:accesspoint:myaccesspoint',
                'Key' => 'my-key',
                'Body' => 'my-body',
            ],
            'output' => [
                'ObjectURL' => 'https://my-bucket.s3.us-east-1.amazonaws.com/my-key'
            ],
            'comments' => [
                'input' => '',
                'output' => 'Simplified example output'
            ],
            'description' => 'The following example uploads an object by referencing the bucket via an S3 accesss point ARN. Result output is simplified for the example.',
            'id' => '',
            'title' => 'To upload an object via an S3 access point ARN'
        ];
        if (isset($examples['PutObject'])) {
            $examples['PutObject'] []= $putObjectExample;
        } else {
            $examples['PutObject'] = [$putObjectExample];
        }

        return $examples;
    }
}
