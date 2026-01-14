<?php

declare(strict_types=1);

namespace App\Core\Domain\Event\Transfer;

use App\Core\Domain\Contracts\Event\Subscriber;

class CompletedSubscriber implements Subscriber
{
    public function __construct()
    {
    }

    public function listen(): array
    {
        return [
            Completed::class,
        ];
    }

    public function process(object $event): void
    {
        assert($event instanceof Completed);

        echo 'Sent Completed Transfer Notification for Transfer ID: ' . $event->getTransferId();
    }
}
