<?php

namespace MediaCloud\Vendor\Aws\Ses;
use MediaCloud\Vendor\Aws\Api\ApiProvider;
use MediaCloud\Vendor\Aws\Api\DocModel;
use MediaCloud\Vendor\Aws\Api\Service;
use MediaCloud\Vendor\Aws\Credentials\CredentialsInterface;

/**
 * This client is used to interact with the **Amazon Simple Email Service (Amazon SES)**.
 *
 * @method \MediaCloud\Vendor\Aws\Result cloneReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cloneReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createConfigurationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConfigurationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createConfigurationSetEventDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConfigurationSetEventDestinationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createConfigurationSetTrackingOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConfigurationSetTrackingOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCustomVerificationEmailTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createReceiptFilter(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createReceiptFilterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createReceiptRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createReceiptRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConfigurationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConfigurationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConfigurationSetEventDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConfigurationSetEventDestinationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConfigurationSetTrackingOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConfigurationSetTrackingOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCustomVerificationEmailTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteIdentityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteIdentityPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteIdentityPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteReceiptFilter(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteReceiptFilterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteReceiptRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteReceiptRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteVerifiedEmailAddress(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteVerifiedEmailAddressAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeActiveReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeActiveReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeConfigurationSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeConfigurationSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeReceiptRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReceiptRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccountSendingEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccountSendingEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCustomVerificationEmailTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getIdentityDkimAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getIdentityDkimAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getIdentityMailFromDomainAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getIdentityMailFromDomainAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getIdentityNotificationAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getIdentityNotificationAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getIdentityPolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getIdentityPoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getIdentityVerificationAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getIdentityVerificationAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSendQuota(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSendQuotaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getSendStatistics(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getSendStatisticsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listConfigurationSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listConfigurationSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCustomVerificationEmailTemplates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCustomVerificationEmailTemplatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listIdentities(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listIdentitiesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listIdentityPolicies(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listIdentityPoliciesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listReceiptFilters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listReceiptFiltersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listReceiptRuleSets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listReceiptRuleSetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTemplates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTemplatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listVerifiedEmailAddresses(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listVerifiedEmailAddressesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putConfigurationSetDeliveryOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putConfigurationSetDeliveryOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putIdentityPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putIdentityPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result reorderReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise reorderReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendBounce(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendBounceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendBulkTemplatedEmail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendBulkTemplatedEmailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendCustomVerificationEmail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendCustomVerificationEmailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendEmail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendEmailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendRawEmail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendRawEmailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendTemplatedEmail(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendTemplatedEmailAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setActiveReceiptRuleSet(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setActiveReceiptRuleSetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setIdentityDkimEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setIdentityDkimEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setIdentityFeedbackForwardingEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setIdentityFeedbackForwardingEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setIdentityHeadersInNotificationsEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setIdentityHeadersInNotificationsEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setIdentityMailFromDomain(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setIdentityMailFromDomainAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setIdentityNotificationTopic(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setIdentityNotificationTopicAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result setReceiptRulePosition(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise setReceiptRulePositionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result testRenderTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise testRenderTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateAccountSendingEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateAccountSendingEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConfigurationSetEventDestination(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConfigurationSetEventDestinationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConfigurationSetReputationMetricsEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConfigurationSetReputationMetricsEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConfigurationSetSendingEnabled(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConfigurationSetSendingEnabledAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConfigurationSetTrackingOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConfigurationSetTrackingOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateCustomVerificationEmailTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCustomVerificationEmailTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateReceiptRule(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateReceiptRuleAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateTemplate(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateTemplateAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result verifyDomainDkim(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise verifyDomainDkimAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result verifyDomainIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise verifyDomainIdentityAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result verifyEmailAddress(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise verifyEmailAddressAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result verifyEmailIdentity(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise verifyEmailIdentityAsync(array $args = [])
 */
class SesClient extends \MediaCloud\Vendor\Aws\AwsClient
{
    /**
     * @deprecated This method will no longer work due to the deprecation of
     *             V2 credentials with SES as of 03/25/2021
     * Create an SMTP password for a given IAM user's credentials.
     *
     * The SMTP username is the Access Key ID for the provided credentials.
     *
     * @link http://docs.aws.amazon.com/ses/latest/DeveloperGuide/smtp-credentials.html#smtp-credentials-convert
     *
     * @param CredentialsInterface $creds
     *
     * @return string
     */
    public static function generateSmtpPassword(CredentialsInterface $creds)
    {
        static $version = "\x02";
        static $algo = 'sha256';
        static $message = 'SendRawEmail';
        $signature = hash_hmac($algo, $message, $creds->getSecretKey(), true);

        return base64_encode($version . $signature);
    }

    /**
     * Create an SMTP password for a given IAM user's credentials.
     *
     * The SMTP username is the Access Key ID for the provided credentials. This
     * utility method is not guaranteed to work indefinitely and is provided as
     * a convenience to customers using the generateSmtpPassword method.  It is
     * not recommended for use in production
     *
     * @link https://docs.aws.amazon.com/ses/latest/DeveloperGuide/smtp-credentials.html#smtp-credentials-convert
     *
     * @param CredentialsInterface $creds
     * @param string $region
     *
     * @return string
     */
    public static function generateSmtpPasswordV4(CredentialsInterface $creds, $region)
    {
        $key = $creds->getSecretKey();

        $date = "11111111";
        $service = "ses";
        $terminal = "aws4_request";
        $message = "SendRawEmail";
        $version = 0x04;

        $signature = self::sign($date, "AWS4" . $key);
        $signature = self::sign($region, $signature);
        $signature = self::sign($service, $signature);
        $signature = self::sign($terminal, $signature);
        $signature = self::sign($message, $signature);
        $signatureAndVersion = pack('c', $version) . $signature;

        return  base64_encode($signatureAndVersion);
    }

    private static function sign($key, $message) {
        return hash_hmac('sha256', $key, $message, true);
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        $b64 = '<div class="alert alert-info">This value will be base64 encoded on your behalf.</div>';

        $docs['shapes']['RawMessage']['append'] = $b64;

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }
}
