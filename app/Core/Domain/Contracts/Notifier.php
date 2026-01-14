<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

interface Notifier
{
    public function notify(string $userId): void;
}
