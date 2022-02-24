<?php

namespace MediaCloud\Vendor\Aws\Acm;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Certificate Manager** service.
 *
 * @method \MediaCloud\Vendor\Aws\Result addTagsToCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsToCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result exportCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise exportCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccountConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccountConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCertificates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCertificatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putAccountConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putAccountConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTagsFromCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsFromCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result renewCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise renewCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result requestCertificate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise requestCertificateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result resendValidationEmail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise resendValidationEmailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateCertificateOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCertificateOptionsAsync(array $args = [])
 */
class AcmClient extends AwsClient {}
