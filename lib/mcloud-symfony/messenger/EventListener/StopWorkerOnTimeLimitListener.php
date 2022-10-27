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
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerRunningEvent;
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerStartedEvent;

/**
 * @author Simon Delicata <simon.delicata@free.fr>
 * @author Tobias Schultze <http://tobion.de>
 */
class StopWorkerOnTimeLimitListener implements EventSubscriberInterface
{
    private $timeLimitInSeconds;
    private $logger;
    private $endTime;

    public function __construct(int $timeLimitInSeconds, LoggerInterface $logger = null)
    {
        $this->timeLimitInSeconds = $timeLimitInSeconds;
        $this->logger = $logger;
    }

    public function onWorkerStarted(): void
    {
        $startTime = microtime(true);
        $this->endTime = $startTime + $this->timeLimitInSeconds;
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($this->endTime < microtime(true)) {
            $event->getWorker()->stop();
            if (null !== $this->logger) {
                $this->logger->info('Worker stopped due to time limit of {timeLimit}s exceeded', ['timeLimit' => $this->timeLimitInSeconds]);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerStartedEvent::class => 'onWorkerStarted',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
