<?php

declare(strict_types=1);

namespace App\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Contracts\WalletRepository as WalletRepositoryInterface;
use App\Core\Domain\LedgerEntry;
use App\Core\Domain\Wallet;
use App\Infra\ORM\LedgerEntry as ORMLedgerEntry;
use App\Infra\ORM\Wallet as ORMWallet;
use Exception;

class WalletRepository implements WalletRepositoryInterface
{
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

    public function getOneById(string $id): Wallet
    {
        $ormWallet = ORMWallet::query()
            ->with('ledgerEntries')
            ->find($id);

        if (! $ormWallet instanceof ORMWallet) {
            throw new Exception("Wallet with id {$id} not found");
        }

        return Wallet::create(
            id: $ormWallet->id,
            userId: $ormWallet->user_id,
            ledgerEntries: $ormWallet->ledgerEntries
                ->map(fn ($ledgerEntry) => LedgerEntry::create(
                    walletId: $ledgerEntry->wallet_id,
                    amount: $ledgerEntry->amount,
                    type: LedgerEntryType::from($ledgerEntry->type),
                    operation: LedgerOperation::from($ledgerEntry->operation),
                    id: $ledgerEntry->id,
                ))
                ->all(),
        );
    }
}
