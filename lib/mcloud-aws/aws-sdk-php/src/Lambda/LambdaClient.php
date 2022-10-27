<?php

namespace MediaCloud\Vendor\Aws\Lambda;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Aws\Middleware;

/**
 * This client is used to interact with AWS Lambda
 *
 * @method \MediaCloud\Vendor\Aws\Result addLayerVersionPermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addLayerVersionPermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result addPermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addPermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEventSourceMapping(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEventSourceMappingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createFunction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFunctionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEventSourceMapping(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEventSourceMappingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteFunction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFunctionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteFunctionCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFunctionCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteFunctionConcurrency(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFunctionConcurrencyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteFunctionEventInvokeConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFunctionEventInvokeConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteLayerVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteLayerVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteProvisionedConcurrencyConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteProvisionedConcurrencyConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAccountSettings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAccountSettingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEventSourceMapping(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEventSourceMappingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getFunction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFunctionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getFunctionCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFunctionCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getFunctionConcurrency(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFunctionConcurrencyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getFunctionConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFunctionConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getFunctionEventInvokeConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getFunctionEventInvokeConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLayerVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLayerVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLayerVersionByArn(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLayerVersionByArnAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getLayerVersionPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getLayerVersionPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getPolicy(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPolicyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getProvisionedConcurrencyConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getProvisionedConcurrencyConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result invoke(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise invokeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result invokeAsync(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise invokeAsyncAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAliases(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAliasesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCodeSigningConfigs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCodeSigningConfigsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listEventSourceMappings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listEventSourceMappingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFunctionEventInvokeConfigs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFunctionEventInvokeConfigsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFunctions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFunctionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listFunctionsByCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listFunctionsByCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listLayerVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listLayerVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listLayers(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listLayersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listProvisionedConcurrencyConfigs(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listProvisionedConcurrencyConfigsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listVersionsByFunction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listVersionsByFunctionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result publishLayerVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishLayerVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result publishVersion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise publishVersionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putFunctionCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putFunctionCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putFunctionConcurrency(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putFunctionConcurrencyAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putFunctionEventInvokeConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putFunctionEventInvokeConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putProvisionedConcurrencyConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putProvisionedConcurrencyConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeLayerVersionPermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeLayerVersionPermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removePermission(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removePermissionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateAlias(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateAliasAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateCodeSigningConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateCodeSigningConfigAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEventSourceMapping(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEventSourceMappingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateFunctionCode(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFunctionCodeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateFunctionConfiguration(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFunctionConfigurationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateFunctionEventInvokeConfig(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFunctionEventInvokeConfigAsync(array $args = [])
 */
class LambdaClient extends AwsClient
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        $list = $this->getHandlerList();
        if (extension_loaded('curl')) {
            $list->appendInit($this->getDefaultCurlOptionsMiddleware());
        }
    }

    /**
     * Provides a middleware that sets default Curl options for the command
     *
     * @return callable
     */
    public function getDefaultCurlOptionsMiddleware()
    {
        return Middleware::mapCommand(function (CommandInterface $cmd) {
            $defaultCurlOptions = [
                CURLOPT_TCP_KEEPALIVE => 1,
            ];
            if (!isset($cmd['@http']['curl'])) {
                $cmd['@http']['curl'] = $defaultCurlOptions;
            } else {
                $cmd['@http']['curl'] += $defaultCurlOptions;
            }
            return $cmd;
        });
    }
}
