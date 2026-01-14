<?php

declare(strict_types=1);

namespace App\Core\Domain\Exceptions;

class InvalidUser extends DomainException
{
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
