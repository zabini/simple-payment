<?php

declare(strict_types=1);

namespace App\Core\Domain\Exceptions;

use Exception;

class DomainException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $errorCode = 'BUSINESS_ERROR',
        private readonly int $statusCode = 422,
        private readonly array $errors = []
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
