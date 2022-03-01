<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Messenger\Bridge\Doctrine\Transport;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\RetryableException;
use MediaCloud\Vendor\Symfony\Component\Messenger\Envelope;
use MediaCloud\Vendor\Symfony\Component\Messenger\Exception\LogicException;
use MediaCloud\Vendor\Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use MediaCloud\Vendor\Symfony\Component\Messenger\Exception\TransportException;
use MediaCloud\Vendor\Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Vincent Touzet <vincent.touzet@gmail.com>
 */
class DoctrineReceiver implements ReceiverInterface, MessageCountAwareInterface, ListableReceiverInterface
{
    private const MAX_RETRIES = 3;
    private $retryingSafetyCounter = 0;
    private $connection;
    private $serializer;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    /**
     * {@inheritdoc}
     */
    public function get(): iterable
    {
        try {
            $doctrineEnvelope = $this->connection->get();
            $this->retryingSafetyCounter = 0; // reset counter
        } catch (RetryableException $exception) {
            // Do nothing when RetryableException occurs less than "MAX_RETRIES"
            // as it will likely be resolved on the next call to get()
            // Problem with concurrent consumers and database deadlocks
            if (++$this->retryingSafetyCounter >= self::MAX_RETRIES) {
                $this->retryingSafetyCounter = 0; // reset counter
                throw new TransportException($exception->getMessage(), 0, $exception);
            }

            return [];
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        if (null === $doctrineEnvelope) {
            return [];
        }

        return [$this->createEnvelopeFromData($doctrineEnvelope)];
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope): void
    {
        try {
            $this->connection->ack($this->findDoctrineReceivedStamp($envelope)->getId());
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reject(Envelope $envelope): void
    {
        try {
            $this->connection->reject($this->findDoctrineReceivedStamp($envelope)->getId());
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageCount(): int
    {
        try {
            return $this->connection->getMessageCount();
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all(int $limit = null): iterable
    {
        try {
            $doctrineEnvelopes = $this->connection->findAll($limit);
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        foreach ($doctrineEnvelopes as $doctrineEnvelope) {
            yield $this->createEnvelopeFromData($doctrineEnvelope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($id): ?Envelope
    {
        try {
            $doctrineEnvelope = $this->connection->find($id);
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        if (null === $doctrineEnvelope) {
            return null;
        }

        return $this->createEnvelopeFromData($doctrineEnvelope);
    }

    private function findDoctrineReceivedStamp(Envelope $envelope): DoctrineReceivedStamp
    {
        /** @var DoctrineReceivedStamp|null $doctrineReceivedStamp */
        $doctrineReceivedStamp = $envelope->last(DoctrineReceivedStamp::class);

        if (null === $doctrineReceivedStamp) {
            throw new LogicException('No DoctrineReceivedStamp found on the Envelope.');
        }

        return $doctrineReceivedStamp;
    }

    private function createEnvelopeFromData(array $data): Envelope
    {
        try {
            $envelope = $this->serializer->decode([
                'body' => $data['body'],
                'headers' => $data['headers'],
            ]);
        } catch (MessageDecodingFailedException $exception) {
            $this->connection->reject($data['id']);

            throw $exception;
        }

        return $envelope->with(
            new DoctrineReceivedStamp($data['id']),
            new TransportMessageIdStamp($data['id'])
        );
    }
}

if (!class_exists(\MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Doctrine\DoctrineReceiver::class, false)) {
    class_alias(DoctrineReceiver::class, \MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Doctrine\DoctrineReceiver::class);
}
