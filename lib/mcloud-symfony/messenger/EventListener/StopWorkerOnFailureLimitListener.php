<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Messenger\EventListener;
use MediaCloud\Vendor\Psr\Log\LoggerInterface;
use MediaCloud\Vendor\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerRunningEvent;
use MediaCloud\Vendor\Symfony\Component\Messenger\Exception\InvalidArgumentException;

/**
 * @author Michel Hunziker <info@michelhunziker.com>
 */
class StopWorkerOnFailureLimitListener implements EventSubscriberInterface
{
    private $maximumNumberOfFailures;
    private $logger;
    private $failedMessages = 0;

    public function __construct(int $maximumNumberOfFailures, LoggerInterface $logger = null)
    {
        $this->maximumNumberOfFailures = $maximumNumberOfFailures;
        $this->logger = $logger;

        if ($maximumNumberOfFailures <= 0) {
            throw new InvalidArgumentException('Failure limit must be greater than zero.');
        }
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        ++$this->failedMessages;
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if (!$event->isWorkerIdle() && $this->failedMessages >= $this->maximumNumberOfFailures) {
            $this->failedMessages = 0;
            $event->getWorker()->stop();

            if (null !== $this->logger) {
                $this->logger->info('Worker stopped due to limit of {count} failed message(s) is reached', ['count' => $this->maximumNumberOfFailures]);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
