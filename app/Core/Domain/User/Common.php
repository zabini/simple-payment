<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\UserKind;

class Common extends User
{

    /** @inheritDoc */
    public static function providesKind(): UserKind
    {
        return UserKind::common;
    }

    /** @inheritDoc */
    public function canTransfer(): bool
    {
        return true;
    }
}
