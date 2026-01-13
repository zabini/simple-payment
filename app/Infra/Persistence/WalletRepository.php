<?php

namespace App\Infra\Persistence;

use App\Core\Domain\Wallet;
use App\Core\Domain\Contracts\WalletRepository as WalletRepositoryInterface;
use App\Infra\ORM\LedgerEntry as ORMLedgerEntry;

class WalletRepository implements WalletRepositoryInterface
{

    /** @inheritDoc */
    public function save(Wallet $wallet): void
    {
        foreach ($wallet->getLedgerEntries() as $ledgerEntry) {
            if (ORMLedgerEntry::query()->find($ledgerEntry->getId()) instanceof ORMLedgerEntry) {
                continue;
            }

            $ormLedgerEntry = new ORMLedgerEntry([
                'id' => $ledgerEntry->getId(),
                'wallet_id' => $ledgerEntry->getWalletId(),
                'amount' => $ledgerEntry->getAmount(),
                'type' => $ledgerEntry->getType()->value,
                'operation' => $ledgerEntry->getOperation()->value,
            ]);

            $ormLedgerEntry->save();
        }
    }
}
