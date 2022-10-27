<?php

namespace MediaCloud\Vendor\Aws\ClientSideMonitoring;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\ResultInterface;
use MediaCloud\Vendor\GuzzleHttp\Psr7\Request;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
interface MonitoringMiddlewareInterface
{

    /**
     * Data for event properties to be sent to the monitoring agent.
     *
     * @param RequestInterface $request
     * @return array
     */
    public static function getRequestData(RequestInterface $request);


    /**
     * Data for event properties to be sent to the monitoring agent.
     *
     * @param ResultInterface|AwsException|\Exception $klass
     * @return array
     */
    public static function getResponseData($klass);

    public function __invoke(CommandInterface $cmd, RequestInterface $request);
}