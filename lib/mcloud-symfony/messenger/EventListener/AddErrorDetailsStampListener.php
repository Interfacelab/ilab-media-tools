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
use MediaCloud\Vendor\Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;

final class AddErrorDetailsStampListener implements EventSubscriberInterface
{
    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $stamp = ErrorDetailsStamp::create($event->getThrowable());
        $previousStamp = $event->getEnvelope()->last(ErrorDetailsStamp::class);

        // Do not append duplicate information
        if (null === $previousStamp || !$previousStamp->equals($stamp)) {
            $event->addStamps($stamp);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must have higher priority than SendFailedMessageForRetryListener
            WorkerMessageFailedEvent::class => ['onMessageFailed', 200],
        ];
    }
}
