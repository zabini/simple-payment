<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\UserKind;

class Seller extends User
{
    public static function providesKind(): UserKind
    {
        return UserKind::seller;
    }

    public function canTransfer(): bool
    {
        return false;
    }
}
