<?php

namespace App\Infra\Persistence;

use App\Core\Domain\Wallet;
use App\Core\Domain\Contracts\WalletRepository as WalletRepositoryInterface;
use App\Infra\ORM\Wallet as ORMWallet;

class WalletRepository implements WalletRepositoryInterface
{

    /** @inheritDoc */
    public function save(Wallet $wallet): void
    {
        $ormUser = new ORMWallet([
            'id' => $wallet->getId(),
            'user_id' => $wallet->getUserId(),
            'balance' => $wallet->getBalance(),
        ]);

        $ormUser->save();
    }
}
