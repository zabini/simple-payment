<?php

declare(strict_types=1);

namespace App\Core\Domain\Event\Transfer;

use App\Core\Domain\Contracts\AsyncJobDispatcher;
use App\Core\Domain\Contracts\Event\Subscriber;
use App\Infra\Async\NotifyPayee as AsyncNotifyPayee;

class CompletedSubscriber implements Subscriber
{
    public function __construct(private AsyncJobDispatcher $jobDispatcher)
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
        $this->jobDispatcher->dispatch(new AsyncNotifyPayee($event->getTransferId()));
    }
}
