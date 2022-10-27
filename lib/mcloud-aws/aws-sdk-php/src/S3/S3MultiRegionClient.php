<?php

namespace MediaCloud\Vendor\Aws\S3;
use MediaCloud\Vendor\Aws\CacheInterface;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Aws\LruArrayCache;
use MediaCloud\Vendor\Aws\MultiRegionClient as BaseClient;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\S3\Exception\PermanentRedirectException;
use MediaCloud\Vendor\GuzzleHttp\Promise;

/**
 * **Amazon Simple Storage Service** multi-region client.
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
class S3MultiRegionClient extends BaseClient implements S3ClientInterface
{
    use S3ClientTrait;

    /** @var CacheInterface */
    private $cache;

    public static function getArguments()
    {
        $args = parent::getArguments();
        $regionDef = $args['region'] + ['default' => function (array &$args) {
            $availableRegions = array_keys($args['partition']['regions']);
            return end($availableRegions);
        }];
        unset($args['region']);

        return $args + [
            'bucket_region_cache' => [
                'type' => 'config',
                'valid' => [CacheInterface::class],
                'doc' => 'Cache of regions in which given buckets are located.',
                'default' => function () { return new LruArrayCache; },
            ],
            'region' => $regionDef,
        ];
    }

    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->cache = $this->getConfig('bucket_region_cache');

        $this->getHandlerList()->prependInit(
            $this->determineRegionMiddleware(),
            'determine_region'
        );
    }

    private function determineRegionMiddleware()
    {
        return function (callable $handler) {
            return function (CommandInterface $command) use ($handler) {
                $cacheKey = $this->getCacheKey($command['Bucket']);
                if (
                    empty($command['@region']) &&
                    $region = $this->cache->get($cacheKey)
                ) {
                    $command['@region'] = $region;
                }

                return Promise\Coroutine::of(function () use (
                    $handler,
                    $command,
                    $cacheKey
                ) {
                    try {
                        yield $handler($command);
                    } catch (PermanentRedirectException $e) {
                        if (empty($command['Bucket'])) {
                            throw $e;
                        }
                        $result = $e->getResult();
                        $region = null;
                        if (isset($result['@metadata']['headers']['x-amz-bucket-region'])) {
                            $region = $result['@metadata']['headers']['x-amz-bucket-region'];
                            $this->cache->set($cacheKey, $region);
                        } else {
                            $region = (yield $this->determineBucketRegionAsync(
                                $command['Bucket']
                            ));
                        }

                        $command['@region'] = $region;
                        yield $handler($command);
                    } catch (AwsException $e) {
                        if ($e->getAwsErrorCode() === 'AuthorizationHeaderMalformed') {
                            $region = $this->determineBucketRegionFromExceptionBody(
                                $e->getResponse()
                            );
                            if (!empty($region)) {
                                $this->cache->set($cacheKey, $region);

                                $command['@region'] = $region;
                                yield $handler($command);
                            } else {
                                throw $e;
                            }
                        } else {
                            throw $e;
                        }
                    }
                });
            };
        };
    }

    public function createPresignedRequest(CommandInterface $command, $expires, array $options = [])
    {
        if (empty($command['Bucket'])) {
            throw new \InvalidArgumentException('The S3\\MultiRegionClient'
                . ' cannot create presigned requests for commands without a'
                . ' specified bucket.');
        }

        /** @var S3ClientInterface $client */
        $client = $this->getClientFromPool(
            $this->determineBucketRegion($command['Bucket'])
        );
        return $client->createPresignedRequest(
            $client->getCommand($command->getName(), $command->toArray()),
            $expires
        );
    }

    public function getObjectUrl($bucket, $key)
    {
        /** @var S3Client $regionalClient */
        $regionalClient = $this->getClientFromPool(
            $this->determineBucketRegion($bucket)
        );

        return $regionalClient->getObjectUrl($bucket, $key);
    }

    public function determineBucketRegionAsync($bucketName)
    {
        $cacheKey = $this->getCacheKey($bucketName);
        if ($cached = $this->cache->get($cacheKey)) {
            return Promise\Create::promiseFor($cached);
        }

        /** @var S3ClientInterface $regionalClient */
        $regionalClient = $this->getClientFromPool();
        return $regionalClient->determineBucketRegionAsync($bucketName)
            ->then(
                function ($region) use ($cacheKey) {
                    $this->cache->set($cacheKey, $region);

                    return $region;
                }
            );
    }

    private function getCacheKey($bucketName)
    {
        return "aws:s3:{$bucketName}:location";
    }
}
