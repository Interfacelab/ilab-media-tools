<?php

namespace MediaCloud\Vendor\Aws\MediaStoreData;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elemental MediaStore Data Plane** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getObjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listItems(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listItemsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putObject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putObjectAsync(array $args = [])
 */
class MediaStoreDataClient extends AwsClient {}
