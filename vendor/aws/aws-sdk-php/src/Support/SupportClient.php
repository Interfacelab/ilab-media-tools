<?php
namespace ILAB_Aws\Support;

use ILAB_Aws\AwsClient;

/**
 * AWS Support client.
 *
 * @method \ILAB_Aws\Result addAttachmentsToSet(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addAttachmentsToSetAsync(array $args = [])
 * @method \ILAB_Aws\Result addCommunicationToCase(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addCommunicationToCaseAsync(array $args = [])
 * @method \ILAB_Aws\Result createCase(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createCaseAsync(array $args = [])
 * @method \ILAB_Aws\Result describeAttachment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeAttachmentAsync(array $args = [])
 * @method \ILAB_Aws\Result describeCases(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeCasesAsync(array $args = [])
 * @method \ILAB_Aws\Result describeCommunications(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeCommunicationsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeServices(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeServicesAsync(array $args = [])
 * @method \ILAB_Aws\Result describeSeverityLevels(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSeverityLevelsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeTrustedAdvisorCheckRefreshStatuses(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrustedAdvisorCheckRefreshStatusesAsync(array $args = [])
 * @method \ILAB_Aws\Result describeTrustedAdvisorCheckResult(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrustedAdvisorCheckResultAsync(array $args = [])
 * @method \ILAB_Aws\Result describeTrustedAdvisorCheckSummaries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrustedAdvisorCheckSummariesAsync(array $args = [])
 * @method \ILAB_Aws\Result describeTrustedAdvisorChecks(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTrustedAdvisorChecksAsync(array $args = [])
 * @method \ILAB_Aws\Result refreshTrustedAdvisorCheck(array $args = [])
 * @method \GuzzleHttp\Promise\Promise refreshTrustedAdvisorCheckAsync(array $args = [])
 * @method \ILAB_Aws\Result resolveCase(array $args = [])
 * @method \GuzzleHttp\Promise\Promise resolveCaseAsync(array $args = [])
 */
class SupportClient extends AwsClient {}
