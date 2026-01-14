<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

interface TransferAuthorizer
{
    public function authorize(string $userId): void;
}
