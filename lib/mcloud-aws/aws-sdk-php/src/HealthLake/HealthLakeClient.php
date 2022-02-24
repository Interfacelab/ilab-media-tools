<?php

namespace MediaCloud\Vendor\Aws\HealthLake;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon HealthLake** service.
 * @method \MediaCloud\Vendor\Aws\Result createFHIRDatastore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFHIRDatastoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteFHIRDatastore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFHIRDatastoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeFHIRDatastore(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeFHIRDatastoreAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeFHIRExportJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeFHIRExportJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeFHIRImportJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeFHIRImportJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFHIRDatastores(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFHIRDatastoresAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFHIRExportJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFHIRExportJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFHIRImportJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFHIRImportJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startFHIRExportJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startFHIRExportJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startFHIRImportJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startFHIRImportJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class HealthLakeClient extends AwsClient {}
