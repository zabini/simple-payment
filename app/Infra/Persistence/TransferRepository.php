<?php

declare(strict_types=1);

namespace App\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\TransferRepository as TransferRepositoryInterface;
use App\Core\Domain\Transfer;
use App\Infra\ORM\Transfer as ORMTransfer;
use Exception;

class TransferRepository implements TransferRepositoryInterface
{
    public function save(Transfer $transfer): void
    {
        ORMTransfer::query()->updateOrCreate(
            ['id' => $transfer->getId()],
            [
                'payer_id' => $transfer->getPayerId(),
                'payee_id' => $transfer->getPayeeId(),
                'amount' => $transfer->getAmount(),
                'status' => $transfer->getStatus()->value,
                'failed_reason' => $transfer->getFailedReason(),
            ]
        );
    }

    public function getOneById(string $id): Transfer
    {
        $ormTransfer = ORMTransfer::query()->find($id);
        if (! $ormTransfer instanceof ORMTransfer) {
            throw new Exception("Transfer with id {$id} not found");
        }

        return new Transfer(
            $ormTransfer->id,
            $ormTransfer->payer_id,
            $ormTransfer->payee_id,
            $ormTransfer->amount,
            TransferStatus::from($ormTransfer->status),
            $ormTransfer->failed_reason
        );
    }
}
