<?php

declare(strict_types=1);

namespace App\Core\Domain;

use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Exceptions\InvalidOperation;
use Ramsey\Uuid\Uuid;

class Transfer
{
    public function __construct(
        private string $id,
        private Wallet $payerWallet,
        private Wallet $payeeWallet,
        private float $amount,
        private TransferStatus $status,
        private ?string $failedReason = null,
    ) {
    }

    public static function createPending(
        Wallet $payerWallet,
        Wallet $payeeWallet,
        float $amount,
        ?string $id = null,
    ): self {
        self::guardAmount($amount);

        return new self(
            $id ?? Uuid::uuid4()->toString(),
            $payerWallet,
            $payeeWallet,
            $amount,
            TransferStatus::pending,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPayerWallet(): Wallet
    {
        return $this->payerWallet;
    }

    public function getPayeeWallet(): Wallet
    {
        return $this->payeeWallet;
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
            throw InvalidOperation::zeroedAmount();
        }
    }
}
