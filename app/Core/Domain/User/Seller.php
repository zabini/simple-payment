<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\UserKind;

class Seller extends User
{

    /** @inheritDoc */
    public static function providesType(): UserKind
    {
        return UserKind::seller;
    }

    /** @inheritDoc */
    public function canTransfer(): bool
    {
        return false;
    }
}
