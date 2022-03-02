<?php

namespace MediaCloud\Vendor\Aws\KafkaConnect;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Managed Streaming for Kafka Connect** service.
 * @method \MediaCloud\Vendor\Aws\Result createConnector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createConnectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCustomPlugin(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCustomPluginAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createWorkerConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createWorkerConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteConnector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteConnectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCustomPlugin(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCustomPluginAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeConnector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeConnectorAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCustomPlugin(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCustomPluginAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeWorkerConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeWorkerConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listConnectors(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listConnectorsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCustomPlugins(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCustomPluginsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listWorkerConfigurations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listWorkerConfigurationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateConnector(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateConnectorAsync(array $args = [])
 */
class KafkaConnectClient extends AwsClient {}
