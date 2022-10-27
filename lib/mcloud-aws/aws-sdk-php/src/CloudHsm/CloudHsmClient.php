<?php

namespace MediaCloud\Vendor\Aws\CloudHsm;
use MediaCloud\Vendor\Aws\Api\ApiProvider;
use MediaCloud\Vendor\Aws\Api\DocModel;
use MediaCloud\Vendor\Aws\Api\Service;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with **AWS CloudHSM**.
 *
 * @method \MediaCloud\Vendor\Aws\Result addTagsToResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHapg(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHapgAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createHsm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createHsmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createLunaClient(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createLunaClientAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHapg(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHapgAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHsm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHsmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteLunaClient(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteLunaClientAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeHapg(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeHapgAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeHsm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeHsmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeLunaClient(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeLunaClientAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getConfigFiles(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getConfigFilesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAvailableZones(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAvailableZonesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHapgs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHapgsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHsms(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHsmsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listLunaClients(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listLunaClientsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyHapg(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyHapgAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyHsm(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyHsmAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyLunaClient(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyLunaClientAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTagsFromResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 */
class CloudHsmClient extends AwsClient {}
