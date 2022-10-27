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
use MediaCloud\Vendor\Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use MediaCloud\Vendor\Symfony\Component\Messenger\Event\WorkerRunningEvent;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class ResetServicesListener implements EventSubscriberInterface
{
    private $servicesResetter;

    public function __construct(ServicesResetter $servicesResetter)
    {
        $this->servicesResetter = $servicesResetter;
    }

    public function resetServices(WorkerRunningEvent $event): void
    {
        if (!$event->isWorkerIdle()) {
            $this->servicesResetter->reset();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => ['resetServices', -1024],
        ];
    }
}
