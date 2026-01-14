<?php

declare(strict_types=1);

namespace App\Core\Domain\Event\Transfer;

use App\Core\Domain\Contracts\Event\Event;

class BaseEvent implements Event
{
    public function __construct(private string $transferId)
    {
    }

    public function getTransferId(): string
    {
        return $this->transferId;
    }
}
