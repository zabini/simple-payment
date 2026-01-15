<?php

declare(strict_types=1);

namespace App\Core\Domain\Exceptions;

class NotificationException extends DomainException
{
    public static function unmappedReason(string $reason): self
    {
        return new self(
            sprintf('Failed to notify for reason: %s', $reason)
        );
    }
}
