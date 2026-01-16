<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\Notifier;

final class SpyNotifier implements Notifier
{
    /** @var string[] */
    private array $notifiedUserIds = [];

    public function notify(string $userId): void
    {
        $this->notifiedUserIds[] = $userId;
    }

    /**
     * @return string[]
     */
    public function notifiedUsers(): array
    {
        return $this->notifiedUserIds;
    }
}
