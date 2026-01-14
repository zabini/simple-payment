<?php

declare(strict_types=1);

namespace App\Core\Domain\Event\Transfer;

use App\Core\Application\Wallet\ProcessTransfer;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\Event\Subscriber;

class PendingCreatedSubscriber implements Subscriber
{
    public function __construct(private ProcessTransferHandler $processTransferHandler)
    {
    }

    public function listen(): array
    {
        return [
            PendingCreated::class,
        ];
    }

    public function process(object $event): void
    {
        assert($event instanceof PendingCreated);

        $this->processTransferHandler->handle(
            new ProcessTransfer($event->getTransferId())
        );
    }
}
