<?php

namespace MediaCloud\Vendor\Aws\signer;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Signer** service.
 * @method \MediaCloud\Vendor\Aws\Result addProfilePermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addProfilePermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result cancelSigningProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelSigningProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeSigningJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSigningJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSigningPlatform(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSigningPlatformAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSigningProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSigningProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listProfilePermissions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listProfilePermissionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSigningJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSigningJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSigningPlatforms(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSigningPlatformsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSigningProfiles(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSigningProfilesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putSigningProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putSigningProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeProfilePermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeProfilePermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result revokeSignature(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise revokeSignatureAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result revokeSigningProfile(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise revokeSigningProfileAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startSigningJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startSigningJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class signerClient extends AwsClient {}
