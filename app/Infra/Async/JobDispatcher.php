<?php

declare(strict_types=1);

namespace App\Infra\Async;

use App\Core\Domain\Contracts\AsyncJobDispatcher;
use Hyperf\AsyncQueue\JobInterface;
use InvalidArgumentException;

use function Hyperf\AsyncQueue\dispatch;

class JobDispatcher implements AsyncJobDispatcher
{
    public function dispatch(object $job, ?int $delay = null, ?int $maxAttempts = null, ?string $pool = null): bool
    {
        if (! $job instanceof JobInterface) {
            throw new InvalidArgumentException('Job must implement Hyperf\AsyncQueue\JobInterface');
        }

        return dispatch($job, $delay, $maxAttempts, $pool);
    }
}
