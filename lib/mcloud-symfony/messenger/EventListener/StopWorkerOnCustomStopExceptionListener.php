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
use MediaCloud\Vendor\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerRunningEvent;
use MediaCloud\Vendor\Symfony\Component\Messenger\Exception\HandlerFailedException;
use MediaCloud\Vendor\Symfony\Component\Messenger\Exception\StopWorkerExceptionInterface;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class StopWorkerOnCustomStopExceptionListener implements EventSubscriberInterface
{
    private $stop = false;

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $th = $event->getThrowable();
        if ($th instanceof StopWorkerExceptionInterface) {
            $this->stop = true;
        }
        if ($th instanceof HandlerFailedException) {
            foreach ($th->getNestedExceptions() as $e) {
                if ($e instanceof StopWorkerExceptionInterface) {
                    $this->stop = true;
                    break;
                }
            }
        }
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($this->stop) {
            $event->getWorker()->stop();
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
