<?php

declare(strict_types=1);

namespace App\Infra\Exception;

use Exception;

class JsonException extends Exception
{
    public static function invalid(): self
    {
        return new self(
            sprintf('Invalid JSON format')
        );
    }
}
