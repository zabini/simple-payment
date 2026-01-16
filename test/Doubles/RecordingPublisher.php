<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\Event\Event;
use App\Core\Domain\Contracts\Event\Publisher;

final class RecordingPublisher implements Publisher
{
    /** @var Event[] */
    private array $events = [];

    public function publish(Event $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return Event[]
     */
    public function releasedEvents(): array
    {
        return $this->events;
    }
}
