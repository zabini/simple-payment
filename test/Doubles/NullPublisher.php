<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\Event\Event;
use App\Core\Domain\Contracts\Event\Publisher;

final class NullPublisher implements Publisher
{
    public function publish(Event $event): void
    {
        // no-op for tests
    }
}
