<?php

namespace MediaCloud\Vendor\Aws\Cloud9;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Cloud9** service.
 * @method \MediaCloud\Vendor\Aws\Result createEnvironmentEC2(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEnvironmentEC2Async(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEnvironmentMembership(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEnvironmentMembershipAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEnvironmentMembership(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEnvironmentMembershipAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironmentMemberships(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentMembershipsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironmentStatus(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentStatusAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEnvironments(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEnvironmentsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnvironment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnvironmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEnvironmentMembership(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEnvironmentMembershipAsync(array $args = [])
 */
class Cloud9Client extends AwsClient {}
