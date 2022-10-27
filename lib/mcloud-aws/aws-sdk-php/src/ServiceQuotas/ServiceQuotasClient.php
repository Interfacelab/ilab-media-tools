<?php

namespace MediaCloud\Vendor\Aws\ServiceQuotas;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Service Quotas** service.
 * @method \MediaCloud\Vendor\Aws\Result associateServiceQuotaTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateServiceQuotaTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteServiceQuotaIncreaseRequestFromTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteServiceQuotaIncreaseRequestFromTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateServiceQuotaTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateServiceQuotaTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAWSDefaultServiceQuota(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAWSDefaultServiceQuotaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAssociationForServiceQuotaTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAssociationForServiceQuotaTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRequestedServiceQuotaChange(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRequestedServiceQuotaChangeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getServiceQuota(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getServiceQuotaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getServiceQuotaIncreaseRequestFromTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getServiceQuotaIncreaseRequestFromTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAWSDefaultServiceQuotas(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAWSDefaultServiceQuotasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRequestedServiceQuotaChangeHistory(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRequestedServiceQuotaChangeHistoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRequestedServiceQuotaChangeHistoryByQuota(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRequestedServiceQuotaChangeHistoryByQuotaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listServiceQuotaIncreaseRequestsInTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listServiceQuotaIncreaseRequestsInTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listServiceQuotas(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listServiceQuotasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listServices(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listServicesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putServiceQuotaIncreaseRequestIntoTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putServiceQuotaIncreaseRequestIntoTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result requestServiceQuotaIncrease(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise requestServiceQuotaIncreaseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class ServiceQuotasClient extends AwsClient {}
