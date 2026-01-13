<?php

declare(strict_types=1);

namespace App\Core\Application\User;

class DepositCommand
{

    /**
     * @param string $userId
     * @param float $amount
     */
    public function __construct(
        private string $userId,
        private float $amount,
    ) {}

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
