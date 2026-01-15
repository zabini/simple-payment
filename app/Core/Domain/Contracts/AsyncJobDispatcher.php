<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

interface AsyncJobDispatcher
{
    public function dispatch(object $job, ?int $delay = null, ?int $maxAttempts = null, ?string $pool = null): bool;
}
