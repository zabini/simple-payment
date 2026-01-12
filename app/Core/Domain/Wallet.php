<?php

declare(strict_types=1);

namespace App\Core\Domain;

use Ramsey\Uuid\Uuid;

class Wallet
{

    /**
     * @param string $id
     * @param string $userId
     * @param float $balance
     */
    public function __construct(
        private string $id,
        private string $userId,
        private float $balance = 0.0
    ) {}

    /**
     * @param string $userId
     * @param string|null $id
     * @param float $balance
     * @return self
     */
    public static function create(string $userId, ?string $id = null, float $balance = 0.0): self
    {
        return new self(
            $id ?? Uuid::uuid4()->toString(),
            $userId,
            $balance
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function deposit(float $amount): void
    {
        $this->guardAmount($amount);

        $this->balance += $amount;
    }

    public function credit(float $amount): void
    {
        $this->guardAmount($amount);

        if ($amount > $this->balance) {
            throw new \DomainException('Insufficient balance to complete credit operation');
        }

        $this->balance -= $amount;
    }

    private function guardAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }
    }
}
