<?php

namespace MediaCloud\Vendor\Aws\Translate;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Translate** service.
 * @method \MediaCloud\Vendor\Aws\Result deleteTerminology(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTerminologyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTextTranslationJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTextTranslationJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTerminology(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTerminologyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importTerminology(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importTerminologyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTerminologies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTerminologiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTextTranslationJobs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTextTranslationJobsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startTextTranslationJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startTextTranslationJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopTextTranslationJob(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopTextTranslationJobAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result translateText(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise translateTextAsync(array $args = [])
 */
class TranslateClient extends AwsClient {}
