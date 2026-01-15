<?php

declare(strict_types=1);

namespace App\Core\Domain;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Exceptions\InvalidOperation;
use Ramsey\Uuid\Uuid;

class LedgerEntry
{
    public function __construct(
        private string $id,
        private string $walletId,
        private float $amount,
        private LedgerEntryType $type,
        private LedgerOperation $operation,
        private ?string $transferId = null
    ) {
    }

    public static function create(
        string $walletId,
        float $amount,
        LedgerEntryType $type,
        LedgerOperation $operation,
        ?string $id = null,
        ?string $transferId = null
    ): self {
        self::guardAmount($amount);

        return new self(
            $id ?? Uuid::uuid4()->toString(),
            $walletId,
            $amount,
            $type,
            $operation,
            $transferId
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getType(): LedgerEntryType
    {
        return $this->type;
    }

    public function getOperation(): LedgerOperation
    {
        return $this->operation;
    }

    public function getTransferId(): ?string
    {
        return $this->transferId;
    }

    public function isCredit(): bool
    {
        return $this->type === LedgerEntryType::credit;
    }

    public function isDebit(): bool
    {
        return $this->type === LedgerEntryType::debit;
    }

    private static function guardAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw InvalidOperation::zeroedAmount();
        }
    }
}
