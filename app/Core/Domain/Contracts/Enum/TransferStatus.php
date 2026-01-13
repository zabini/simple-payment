<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Enum;

enum TransferStatus: string
{
    case pending = 'pending';
    case completed = 'completed';
    case failed = 'failed';
}
