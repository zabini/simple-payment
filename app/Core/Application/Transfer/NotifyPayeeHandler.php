<?php

declare(strict_types=1);

namespace App\Core\Application\Transfer;

use App\Core\Domain\Contracts\Notifier;
use App\Core\Domain\Contracts\TransferRepository;

class NotifyPayeeHandler
{
    public function __construct(
        private TransferRepository $transferRepository,
        private Notifier $notifier
    ) {
    }

    public function handle(NotifyPayee $command)
    {
        $transfer = $this->transferRepository->getOneById($command->getTransferId());
        $this->notifier->notify($transfer->getPayeeWallet()->getUserId());
    }
}
