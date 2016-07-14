<?php
namespace ILAB_Aws\Route53Domains;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Route 53 Domains** service.
 *
 * @method \ILAB_Aws\Result checkDomainAvailability(array $args = [])
 * @method \GuzzleHttp\Promise\Promise checkDomainAvailabilityAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteTagsForDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTagsForDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result disableDomainAutoRenew(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableDomainAutoRenewAsync(array $args = [])
 * @method \ILAB_Aws\Result disableDomainTransferLock(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableDomainTransferLockAsync(array $args = [])
 * @method \ILAB_Aws\Result enableDomainAutoRenew(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableDomainAutoRenewAsync(array $args = [])
 * @method \ILAB_Aws\Result enableDomainTransferLock(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableDomainTransferLockAsync(array $args = [])
 * @method \ILAB_Aws\Result getContactReachabilityStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getContactReachabilityStatusAsync(array $args = [])
 * @method \ILAB_Aws\Result getDomainDetail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDomainDetailAsync(array $args = [])
 * @method \ILAB_Aws\Result getOperationDetail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getOperationDetailAsync(array $args = [])
 * @method \ILAB_Aws\Result listDomains(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDomainsAsync(array $args = [])
 * @method \ILAB_Aws\Result listOperations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listOperationsAsync(array $args = [])
 * @method \ILAB_Aws\Result listTagsForDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result registerDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result resendContactReachabilityEmail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise resendContactReachabilityEmailAsync(array $args = [])
 * @method \ILAB_Aws\Result retrieveDomainAuthCode(array $args = [])
 * @method \GuzzleHttp\Promise\Promise retrieveDomainAuthCodeAsync(array $args = [])
 * @method \ILAB_Aws\Result transferDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise transferDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result updateDomainContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDomainContactAsync(array $args = [])
 * @method \ILAB_Aws\Result updateDomainContactPrivacy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDomainContactPrivacyAsync(array $args = [])
 * @method \ILAB_Aws\Result updateDomainNameservers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDomainNameserversAsync(array $args = [])
 * @method \ILAB_Aws\Result updateTagsForDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateTagsForDomainAsync(array $args = [])
 */
class Route53DomainsClient extends AwsClient {}
