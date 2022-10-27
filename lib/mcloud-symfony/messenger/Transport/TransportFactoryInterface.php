<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Messenger\Transport;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Creates a Messenger transport.
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
interface TransportFactoryInterface
{
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface;

    public function supports(string $dsn, array $options): bool;
}
