<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Event;

interface Publisher
{
    public function publish(Event $event): void;
}
