<?php
namespace ILAB_Aws\S3;

use ILAB_Aws\CacheInterface;
use ILAB_Aws\CommandInterface;
use ILAB_Aws\LruArrayCache;
use ILAB_Aws\MultiRegionClient as BaseClient;
use ILAB_Aws\S3\Exception\PermanentRedirectException;
use GuzzleHttp\Promise;

/**
 * **Amazon Simple Storage Service** multi-region client.
 *
 * @method \ILAB_Aws\Result abortMultipartUpload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise abortMultipartUploadAsync(array $args = [])
 * @method \ILAB_Aws\Result completeMultipartUpload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise completeMultipartUploadAsync(array $args = [])
 * @method \ILAB_Aws\Result copyObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise copyObjectAsync(array $args = [])
 * @method \ILAB_Aws\Result createBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBucketAsync(array $args = [])
 * @method \ILAB_Aws\Result createMultipartUpload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createMultipartUploadAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucketCors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketCorsAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucketLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketLifecycleAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketPolicyAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucketReplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketReplicationAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucketTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketTaggingAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteBucketWebsite(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketWebsiteAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteObjectAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteObjects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteObjectsAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketAccelerateConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketAccelerateConfigurationAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketAclAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketCors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketCorsAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLifecycleAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketLifecycleConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLifecycleConfigurationAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketLocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLocationAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketLogging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLoggingAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketNotification(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketNotificationAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketNotificationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketNotificationConfigurationAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketPolicyAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketReplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketReplicationAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketRequestPayment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketRequestPaymentAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketTaggingAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketVersioning(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketVersioningAsync(array $args = [])
 * @method \ILAB_Aws\Result getBucketWebsite(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketWebsiteAsync(array $args = [])
 * @method \ILAB_Aws\Result getObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectAsync(array $args = [])
 * @method \ILAB_Aws\Result getObjectAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectAclAsync(array $args = [])
 * @method \ILAB_Aws\Result getObjectTorrent(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectTorrentAsync(array $args = [])
 * @method \ILAB_Aws\Result headBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise headBucketAsync(array $args = [])
 * @method \ILAB_Aws\Result headObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise headObjectAsync(array $args = [])
 * @method \ILAB_Aws\Result listBuckets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBucketsAsync(array $args = [])
 * @method \ILAB_Aws\Result listMultipartUploads(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listMultipartUploadsAsync(array $args = [])
 * @method \ILAB_Aws\Result listObjectVersions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectVersionsAsync(array $args = [])
 * @method \ILAB_Aws\Result listObjects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectsAsync(array $args = [])
 * @method \ILAB_Aws\Result listObjectsV2(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectsV2Async(array $args = [])
 * @method \ILAB_Aws\Result listParts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPartsAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketAccelerateConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketAccelerateConfigurationAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketAclAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketCors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketCorsAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketLifecycleAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketLifecycleConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketLifecycleConfigurationAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketLogging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketLoggingAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketNotification(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketNotificationAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketNotificationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketNotificationConfigurationAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketPolicyAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketReplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketReplicationAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketRequestPayment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketRequestPaymentAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketTaggingAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketVersioning(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketVersioningAsync(array $args = [])
 * @method \ILAB_Aws\Result putBucketWebsite(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketWebsiteAsync(array $args = [])
 * @method \ILAB_Aws\Result putObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectAsync(array $args = [])
 * @method \ILAB_Aws\Result putObjectAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectAclAsync(array $args = [])
 * @method \ILAB_Aws\Result restoreObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise restoreObjectAsync(array $args = [])
 * @method \ILAB_Aws\Result uploadPart(array $args = [])
 * @method \GuzzleHttp\Promise\Promise uploadPartAsync(array $args = [])
 * @method \ILAB_Aws\Result uploadPartCopy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise uploadPartCopyAsync(array $args = [])
 */
class S3MultiRegionClient extends BaseClient implements S3ClientInterface
{
    use S3ClientTrait {
        determineBucketRegionAsync as private lookupBucketRegion;
    }

    /** @var CacheInterface */
    private $cache;

    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['region']['default'] = 'us-east-1';

        return $args + [
            'bucket_region_cache' => [
                'type' => 'config',
                'valid' => [CacheInterface::class],
                'doc' => 'Cache of regions in which given buckets are located.',
                'default' => function () { return new LruArrayCache; },
            ],
        ];
    }

    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->cache = $this->getConfig('bucket_region_cache');
    }

    public function executeAsync(CommandInterface $c)
    {
        return Promise\coroutine(function () use ($c) {
            if ($region = $this->cache->get($this->getCacheKey($c['Bucket']))) {
                $c = $this->getRegionalizedCommand($c, $region);
            }

            try {
                yield parent::executeAsync($c);
            } catch (PermanentRedirectException $e) {
                if (empty($c['Bucket'])) {
                    throw $e;
                }
                $region = (yield $this->lookupBucketRegion($c['Bucket']));
                $this->cache->set($this->getCacheKey($c['Bucket']), $region);
                $c = $this->getRegionalizedCommand($c, $region);
                yield parent::executeAsync($c);
            }
        });
    }

    public function createPresignedRequest(CommandInterface $command, $expires)
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
        if ($cached = $this->cache->get($this->getCacheKey($bucketName))) {
            return Promise\promise_for($cached);
        }

        return $this->lookupBucketRegion($bucketName)
            ->then(function ($region) use ($bucketName) {
                $this->cache->set($this->getCacheKey($bucketName), $region);

                return $region;
            });
    }

    private function getRegionalizedCommand(CommandInterface $command, $region)
    {
        return $this->getClientFromPool($region)
            ->getCommand($command->getName(), $command->toArray());
    }

    private function getCacheKey($bucketName)
    {
        return "aws:s3:{$bucketName}:location";
    }
}
