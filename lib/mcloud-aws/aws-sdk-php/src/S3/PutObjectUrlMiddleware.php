<?php

namespace MediaCloud\Vendor\Aws\S3;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Aws\ResultInterface;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * Injects ObjectURL into the result of the PutObject operation.
 *
 * @internal
 */
class PutObjectUrlMiddleware
{
    /** @var callable  */
    private $nextHandler;

    /**
     * Create a middleware wrapper function.
     *
     * @return callable
     */
    public static function wrap()
    {
        return function (callable $handler) {
            return new self($handler);
        };
    }

    /**
     * @param callable $nextHandler Next handler to invoke.
     */
    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    public function __invoke(CommandInterface $command, RequestInterface $request = null)
    {
        $next = $this->nextHandler;
        return $next($command, $request)->then(
            function (ResultInterface $result) use ($command) {
                $name = $command->getName();
                switch ($name) {
                    case 'PutObject':
                    case 'CopyObject':
                        $result['ObjectURL'] = isset($result['@metadata']['effectiveUri'])
                            ? $result['@metadata']['effectiveUri']
                            : null;
                        break;
                    case 'CompleteMultipartUpload':
                        $result['ObjectURL'] = $result['Location'];
                        break;
                }
                return $result;
            }
        );
    }
}
