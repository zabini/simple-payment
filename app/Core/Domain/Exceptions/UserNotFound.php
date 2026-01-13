<?php

declare(strict_types=1);

namespace App\Core\Domain\Exceptions;

use Exception;

class UserNotFound extends Exception
{
    public static function withId(string $id): self
    {
        return new self(
            sprintf('Provided user id (%s) was not found', $id)
        );
    }
}
