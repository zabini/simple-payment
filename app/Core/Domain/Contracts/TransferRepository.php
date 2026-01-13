<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

use App\Core\Domain\Transfer;

interface TransferRepository
{
    public function save(Transfer $transfer): void;

    public function getOneById(string $id): Transfer;
}
