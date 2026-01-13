<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Wallet;

class Common extends User
{
    public static function make(
        ?string $id,
        string $fullName,
        DocumentType $documentType,
        string $document,
        string $email,
        string $password,
        Wallet $wallet,
    ): self {
        return new self(
            $id,
            $fullName,
            UserKind::common,
            $documentType,
            $document,
            $email,
            $password,
            $wallet
        );
    }

    public function canTransfer(): bool
    {
        return true;
    }
}
