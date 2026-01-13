<?php

namespace App\Core\Domain\Exceptions;

use Exception;

class UserNotFound extends Exception
{

    /**
     * @param string $id
     * @return self
     */
    public static function withId(string $id): self
    {
        return new self(
            sprintf('Provided user id (%s) was not found', $id)
        );
    }
}
