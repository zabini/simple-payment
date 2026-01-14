<?php

declare(strict_types=1);

namespace App\Core\Domain\Event;

use App\Core\Application\Wallet\ProcessTransfer;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\Event\Subscriber;

class PendingTransferCreatedSubscriber implements Subscriber
{
    public function __construct(private ProcessTransferHandler $processTransferHandler)
    {
    }

    public function listen(): array
    {
        return [
            PendingTransferCreated::class,
        ];
    }

    public function process(object $event): void
    {
        assert($event instanceof PendingTransferCreated);

        $this->processTransferHandler->handle(
            new ProcessTransfer($event->getTransferId())
        );
    }
}
