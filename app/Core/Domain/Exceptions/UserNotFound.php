<?php

namespace App\Core\Domain\Exceptions;

use Exception;

class UserNotFound extends Exception
{

    /**
     * @param integer $id
     * @return self
     */
    public static function withId(int $id): self
    {
        return new self(
            sprintf('Provided user id (%s) was not found', $id)
        );
    }
}
