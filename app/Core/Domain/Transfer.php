<?php

declare(strict_types=1);

namespace App\Core\Domain;

use App\Core\Domain\Contracts\Enum\TransferStatus;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class Transfer
{
    public function __construct(
        private string $id,
        private string $payerWalletId,
        private string $payeeWalletId,
        private float $amount,
        private TransferStatus $status,
        private ?string $failedReason = null
    ) {
    }

    public static function createPending(
        string $payerWalletId,
        string $payeeWalletId,
        float $amount,
        ?string $id = null
    ): self {
        self::guardAmount($amount);

        return new self(
            $id ?? Uuid::uuid4()->toString(),
            $payerWalletId,
            $payeeWalletId,
            $amount,
            TransferStatus::pending,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPayerWalletId(): string
    {
        return $this->payerWalletId;
    }

    public function getPayeeWalletId(): string
    {
        return $this->payeeWalletId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getStatus(): TransferStatus
    {
        return $this->status;
    }

    public function getFailedReason(): ?string
    {
        return $this->failedReason;
    }

    public function complete(): void
    {
        $this->status = TransferStatus::completed;
        $this->failedReason = null;
    }

    public function fail(string $reason): void
    {
        $this->status = TransferStatus::failed;
        $this->failedReason = $reason;
    }

    public function isntProcessable(): bool
    {
        return $this->getStatus() !== TransferStatus::pending;
    }

    private static function guardAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }
    }
}
