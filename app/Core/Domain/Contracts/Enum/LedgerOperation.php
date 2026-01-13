<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Enum;

enum LedgerOperation: string
{
    case manual = 'manual';
    case transfer = 'transfer';
}
