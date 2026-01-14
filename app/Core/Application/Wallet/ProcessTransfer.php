<?php

declare(strict_types=1);

namespace App\Core\Application\Wallet;

class ProcessTransfer
{
    public function __construct(
        private string $transferId
    ) {
    }

    public function getTransferId(): string
    {
        return $this->transferId;
    }
}
