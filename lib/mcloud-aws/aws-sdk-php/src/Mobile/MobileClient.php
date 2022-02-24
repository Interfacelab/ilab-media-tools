<?php

namespace MediaCloud\Vendor\Aws\Mobile;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Mobile** service.
 * @method \MediaCloud\Vendor\Aws\Result createProject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createProjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteProject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteProjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeBundle(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeBundleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeProject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeProjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportBundle(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportBundleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportProject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportProjectAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listBundles(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listBundlesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listProjects(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listProjectsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateProject(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateProjectAsync(array $args = [])
 */
class MobileClient extends AwsClient {}
