<?php

namespace MediaCloud\Vendor\Aws\ServerlessApplicationRepository;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWSServerlessApplicationRepository** service.
 * @method \MediaCloud\Vendor\Aws\Result createApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createApplicationVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createApplicationVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCloudFormationChangeSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCloudFormationChangeSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCloudFormationTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCloudFormationTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getApplicationPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getApplicationPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCloudFormationTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCloudFormationTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listApplicationDependencies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listApplicationDependenciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listApplicationVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listApplicationVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listApplications(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putApplicationPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putApplicationPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result unshareApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise unshareApplicationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateApplication(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 */
class ServerlessApplicationRepositoryClient extends AwsClient {}
