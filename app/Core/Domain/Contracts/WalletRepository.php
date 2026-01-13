<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

use App\Core\Domain\Wallet;

interface WalletRepository
{
    public function save(Wallet $wallet): void;
}
