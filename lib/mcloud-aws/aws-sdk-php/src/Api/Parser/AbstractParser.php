<?php

namespace MediaCloud\Vendor\Aws\Api\Parser;
use MediaCloud\Vendor\Aws\Api\Service;
use MediaCloud\Vendor\Aws\Api\StructureShape;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Aws\ResultInterface;
use MediaCloud\Vendor\Psr\Http\Message\ResponseInterface;
use MediaCloud\Vendor\Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
abstract class AbstractParser
{
    /** @var \MediaCloud\Vendor\Aws\Api\Service Representation of the service API*/
    protected $api;

    /** @var callable */
    protected $parser;

    /**
     * @param Service $api Service description.
     */
    public function __construct(Service $api)
    {
        $this->api = $api;
    }

    /**
     * @param CommandInterface  $command  Command that was executed.
     * @param ResponseInterface $response Response that was received.
     *
     * @return ResultInterface
     */
    abstract public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    );

    abstract public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    );
}
