<?php

declare(strict_types=1);

namespace App\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\TransferRepository as TransferRepositoryInterface;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\LedgerEntry;
use App\Core\Domain\Transfer;
use App\Core\Domain\Wallet;
use App\Infra\ORM\LedgerEntry as ORMLedgerEntry;
use App\Infra\ORM\Transfer as ORMTransfer;
use App\Infra\ORM\Wallet as ORMWallet;

class TransferRepository implements TransferRepositoryInterface
{
    public function save(Transfer $transfer): void
    {
        $this->transferQuery()->updateOrCreate(
            ['id' => $transfer->getId()],
            [
                'payer_wallet_id' => $transfer->getPayerWallet()->getId(),
                'payee_wallet_id' => $transfer->getPayeeWallet()->getId(),
                'amount' => $transfer->getAmount(),
                'status' => $transfer->getStatus()->value,
                'failed_reason' => $transfer->getFailedReason(),
            ]
        );
    }

    public function getOneById(string $id): Transfer
    {
        $ormTransfer = $this->transferQuery()
            ->with([
                'payerWallet.ledgerEntries',
                'payeeWallet.ledgerEntries',
            ])
            ->find($id);
        if (! $ormTransfer instanceof ORMTransfer) {
            throw NotFound::entityWithId('transfer', $id);
        }

        $payerWallet = $this->hydrateWallet($ormTransfer->payerWallet);
        $payeeWallet = $this->hydrateWallet($ormTransfer->payeeWallet);

        return new Transfer(
            $ormTransfer->id,
            $payerWallet,
            $payeeWallet,
            $ormTransfer->amount,
            TransferStatus::from($ormTransfer->status),
            $ormTransfer->failed_reason,
        );
    }

    protected function transferQuery()
    {
        return ORMTransfer::query();
    }

    private function hydrateWallet(ORMWallet $ormWallet): Wallet
    {
        return Wallet::create(
            id: $ormWallet->id,
            userId: $ormWallet->user_id,
            ledgerEntries: $ormWallet->ledgerEntries
                ->map(fn (ORMLedgerEntry $ledgerEntry) => LedgerEntry::create(
                    walletId: $ledgerEntry->wallet_id,
                    amount: $ledgerEntry->amount,
                    type: LedgerEntryType::from($ledgerEntry->type),
                    operation: LedgerOperation::from($ledgerEntry->operation),
                    id: $ledgerEntry->id,
                    transferId: $ledgerEntry->transfer_id
                ))
                ->all(),
        );
    }
}
