<?php

namespace MediaCloud\Vendor\Aws\Macie;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Macie** service.
 * @method \MediaCloud\Vendor\Aws\Result associateMemberAccount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateMemberAccountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result associateS3Resources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateS3ResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateMemberAccount(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateMemberAccountAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateS3Resources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateS3ResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listMemberAccounts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listMemberAccountsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listS3Resources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listS3ResourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateS3Resources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateS3ResourcesAsync(array $args = [])
 */
class MacieClient extends AwsClient {}
