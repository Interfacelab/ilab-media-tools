<?php

namespace MediaCloud\Vendor\Aws\EC2InstanceConnect;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS EC2 Instance Connect** service.
 * @method \MediaCloud\Vendor\Aws\Result sendSSHPublicKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendSSHPublicKeyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendSerialConsoleSSHPublicKey(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendSerialConsoleSSHPublicKeyAsync(array $args = [])
 */
class EC2InstanceConnectClient extends AwsClient {}
