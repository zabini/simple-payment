<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Enum;

enum UserKind: string
{
    case common = 'common';
    case seller = 'seller';
}
