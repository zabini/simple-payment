<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\WalletRepository;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\Wallet;

final class InMemoryWalletRepository implements WalletRepository
{
    /** @var array<string,Wallet> */
    private array $byId = [];

    public function save(Wallet $wallet): void
    {
        $this->byId[$wallet->getId()] = $wallet;
    }

    public function getOneById(string $id): Wallet
    {
        if (! isset($this->byId[$id])) {
            throw NotFound::entityWithId('wallet', $id);
        }

        return $this->byId[$id];
    }
}
