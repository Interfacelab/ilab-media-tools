<?php
namespace ILAB_Aws\DirectConnect;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Direct Connect** service.
 *
 * @method \ILAB_Aws\Result allocateConnectionOnInterconnect(array $args = [])
 * @method \GuzzleHttp\Promise\Promise allocateConnectionOnInterconnectAsync(array $args = [])
 * @method \ILAB_Aws\Result allocatePrivateVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise allocatePrivateVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result allocatePublicVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise allocatePublicVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result confirmConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise confirmConnectionAsync(array $args = [])
 * @method \ILAB_Aws\Result confirmPrivateVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise confirmPrivateVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result confirmPublicVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise confirmPublicVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result createConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createConnectionAsync(array $args = [])
 * @method \ILAB_Aws\Result createInterconnect(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createInterconnectAsync(array $args = [])
 * @method \ILAB_Aws\Result createPrivateVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createPrivateVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result createPublicVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createPublicVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConnectionAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteInterconnect(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteInterconnectAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteVirtualInterface(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteVirtualInterfaceAsync(array $args = [])
 * @method \ILAB_Aws\Result describeConnectionLoa(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeConnectionLoaAsync(array $args = [])
 * @method \ILAB_Aws\Result describeConnections(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeConnectionsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeConnectionsOnInterconnect(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeConnectionsOnInterconnectAsync(array $args = [])
 * @method \ILAB_Aws\Result describeInterconnectLoa(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeInterconnectLoaAsync(array $args = [])
 * @method \ILAB_Aws\Result describeInterconnects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeInterconnectsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeLocations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeLocationsAsync(array $args = [])
 * @method \ILAB_Aws\Result describeVirtualGateways(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeVirtualGatewaysAsync(array $args = [])
 * @method \ILAB_Aws\Result describeVirtualInterfaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeVirtualInterfacesAsync(array $args = [])
 */
class DirectConnectClient extends AwsClient {}
