<?php

namespace MediaCloud\Vendor\Aws\CloudControlApi;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Cloud Control API** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelResourceRequest(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelResourceRequestAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getResourceRequestStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getResourceRequestStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listResourceRequests(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResourceRequestsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listResources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateResourceAsync(array $args = [])
 */
class CloudControlApiClient extends AwsClient {}
