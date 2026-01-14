<?php

declare(strict_types=1);

namespace App\Core\Domain\Exceptions;

class NotFound extends DomainException
{
    public static function entityWithId(string $entity, string $id): self
    {
        return new self(
            sprintf('Provided %s id (%s) was not found', $entity, $id)
        );
    }
}
