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
        private string $payerId,
        private string $payeeId,
        private float $amount,
        private TransferStatus $status,
        private ?string $failedReason = null
    ) {
    }

    public static function createPending(
        string $payerId,
        string $payeeId,
        float $amount,
        ?string $id = null
    ): self {
        self::guardAmount($amount);

        return new self(
            $id ?? Uuid::uuid4()->toString(),
            $payerId,
            $payeeId,
            $amount,
            TransferStatus::pending,
        );
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getStatus(): TransferStatus
    {
        return $this->status;
    }

    public function getFailedReason(): ?string
    {
        return $this->failedReason;
    }

    private static function guardAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }
    }
}
