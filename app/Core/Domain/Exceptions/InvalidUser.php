<?php

namespace App\Core\Domain\Exceptions;

use Exception;

class InvalidUser extends Exception
{

    /**
     * @param string $type
     * @return self
     */
    public static function invalidUserType(string $type): self
    {
        return new self(
            sprintf('Provided user type (%s) is invalid', $type)
        );
    }

    public static function invalidDocumentType(string $type): self
    {
        return new self(
            sprintf('Provided document type (%s) is invalid', $type)
        );
    }
}
