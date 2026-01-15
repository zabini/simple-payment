<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\Transfer;

final class InMemoryTransferRepository implements TransferRepository
{
    /** @var array<string,Transfer> */
    private array $byId = [];

    public function save(Transfer $transfer): void
    {
        $this->byId[$transfer->getId()] = $transfer;
    }

    public function getOneById(string $id): Transfer
    {
        if (! isset($this->byId[$id])) {
            throw NotFound::entityWithId('transfer', $id);
        }

        return $this->byId[$id];
    }
}
