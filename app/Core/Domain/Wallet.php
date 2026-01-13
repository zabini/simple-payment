<?php

declare(strict_types=1);

namespace App\Core\Domain;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Exceptions\InvalidOperation;
use Ramsey\Uuid\Uuid;

class Wallet
{
    private float $balance = 0.0;

    /**
     * @param LedgerEntry[] $ledgerEntries
     */
    public function __construct(
        private string $id,
        private string $userId,
        private array $ledgerEntries = []
    ) {
        $this->balance = $this->calculateBalance();
    }

    /**
     * @param LedgerEntry[] $ledgerEntries
     */
    public static function create(string $userId, ?string $id = null, array $ledgerEntries = []): self
    {
        return new self(
            $id ?? Uuid::uuid4()->toString(),
            $userId,
            $ledgerEntries
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

    /**
     * @return LedgerEntry[]
     */
    public function getLedgerEntries(): array
    {
        return $this->ledgerEntries;
    }

    public function deposit(float $amount): void
    {
        $this->credit($amount);
    }

    public function credit(float $amount): void
    {
        $this->guardAmount($amount);

        $this->appendEntry(
            LedgerEntry::create(
                $this->id,
                $amount,
                LedgerEntryType::credit,
                LedgerOperation::manual
            )
        );
    }

    public function hasEnoughFunds(float $amount): bool
    {
        $this->guardAmount($amount);

        if ($amount > $this->balance) {
            throw InvalidOperation::noEnoughFunds();
        }

        return true;
    }

    private function guardAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw InvalidOperation::zeroedAmount();
        }
    }

    private function appendEntry(LedgerEntry $entry): void
    {
        $this->ledgerEntries[] = $entry;
        $this->balance = $this->calculateBalance();
    }

    private function calculateBalance(): float
    {
        $balance = 0.0;
        foreach ($this->ledgerEntries as $entry) {
            if (! $entry instanceof LedgerEntry) {
                continue;
            }

            $balance += $entry->isCredit() ? $entry->getAmount() : -$entry->getAmount();
        }

        return $balance;
    }
}
