<?php

namespace MediaCloud\Vendor\Aws\Arn\S3;
use MediaCloud\Vendor\Aws\Arn\ArnInterface;

/**
 * @internal
 */
interface BucketArnInterface extends ArnInterface
{
    public function getBucketName();
}
