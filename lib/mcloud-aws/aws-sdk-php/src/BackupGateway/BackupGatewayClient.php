<?php

namespace MediaCloud\Vendor\Aws\BackupGateway;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Backup Gateway** service.
 * @method \MediaCloud\Vendor\Aws\Result associateGatewayToServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateGatewayToServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createGateway(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGatewayAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteGateway(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteGatewayAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteHypervisor(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteHypervisorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateGatewayFromServer(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateGatewayFromServerAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result importHypervisorConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importHypervisorConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listGateways(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listGatewaysAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listHypervisors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listHypervisorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listVirtualMachines(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listVirtualMachinesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putMaintenanceStartTime(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putMaintenanceStartTimeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result testHypervisorConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise testHypervisorConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateGatewayInformation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateGatewayInformationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateHypervisor(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateHypervisorAsync(array $args = [])
 */
class BackupGatewayClient extends AwsClient {}
