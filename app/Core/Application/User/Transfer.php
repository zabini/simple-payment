<?php

declare(strict_types=1);

namespace App\Core\Application\User;

class Transfer
{
    public function __construct(
        private string $payerId,
        private string $payeeId,
        private float $amount
    ) {
    }

    public function getPayerId(): string
    {
        return $this->payerId;
    }

    public function getPayeeId(): string
    {
        return $this->payeeId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
