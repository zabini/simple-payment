<?php

declare(strict_types=1);

namespace App\Infra\Event;

use App\Core\Domain\Contracts\Event\Event as DomainEvent;
use App\Core\Domain\Contracts\Event\Publisher as DomainEventPublisher;
use Hyperf\Event\EventDispatcher;

final class Publisher implements DomainEventPublisher
{
    public function __construct(private EventDispatcher $dispatcher)
    {
    }

    public function publish(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
