<?php
namespace ILAB_Aws\Acm;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Certificate Manager** service.
 *
 * @method \ILAB_Aws\Result addTagsToCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsToCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result describeCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result getCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result listCertificates(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listCertificatesAsync(array $args = [])
 * @method \ILAB_Aws\Result listTagsForCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result removeTagsFromCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsFromCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result requestCertificate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise requestCertificateAsync(array $args = [])
 * @method \ILAB_Aws\Result resendValidationEmail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise resendValidationEmailAsync(array $args = [])
 */
class AcmClient extends AwsClient {}
