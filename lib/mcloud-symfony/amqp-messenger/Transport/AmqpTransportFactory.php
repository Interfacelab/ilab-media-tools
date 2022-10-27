<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Messenger\Bridge\Amqp\Transport;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class AmqpTransportFactory implements TransportFactoryInterface
{
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        unset($options['transport_name']);

        return new AmqpTransport(Connection::fromDsn($dsn, $options), $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'amqp://') || 0 === strpos($dsn, 'amqps://');
    }
}

if (!class_exists(\MediaCloud\Vendor\Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransportFactory::class, false)) {
    class_alias(AmqpTransportFactory::class, \MediaCloud\Vendor\Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransportFactory::class);
}
