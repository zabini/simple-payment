<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

use App\Core\Domain\Wallet;

interface WalletRepository
{

    /**
     * @param Wallet $wallet
     */
    public function save(Wallet $wallet): void;
}
