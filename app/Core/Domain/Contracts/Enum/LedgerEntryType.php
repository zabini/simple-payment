<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Enum;

enum LedgerEntryType: string
{
    case credit = 'credit';
    case debit = 'debit';
}
