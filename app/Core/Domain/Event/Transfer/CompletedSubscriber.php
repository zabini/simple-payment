<?php

declare(strict_types=1);

namespace App\Core\Domain\Event\Transfer;

use App\Core\Application\Transfer\NotifyPayee;
use App\Core\Application\Transfer\NotifyPayeeHandler;
use App\Core\Domain\Contracts\Event\Subscriber;

class CompletedSubscriber implements Subscriber
{
    public function __construct(private NotifyPayeeHandler $notifyPayeeHandler) {}

    public function listen(): array
    {
        return [
            Completed::class,
        ];
    }

    public function process(object $event): void
    {
        assert($event instanceof Completed);

        $this->notifyPayeeHandler->handle(
            new NotifyPayee($event->getTransferId())
        );
    }
}
