<?php

declare(strict_types=1);

namespace App\Core\Domain\Exceptions;

use Exception;

class InvalidOperation extends Exception
{
    public static function sameUser(): self
    {
        return new self(
            sprintf('Payer and Payee must differ')
        );
    }

    public static function userType(): self
    {
        return new self(
            sprintf('User type is not able to transfer money')
        );
    }

    public static function zeroedAmount(): self
    {
        return new self(
            sprintf('Amount must be greater than zero')
        );
    }

    public static function noEnoughFunds(): self
    {
        return new self(
            sprintf('No enough funds')
        );
    }

    public static function unprocessableTransfer(): self
    {
        return new self(
            sprintf('Transfer is not processable')
        );
    }

    public static function fromExternalReason(string $reason): self
    {
        return new self(
            sprintf('Transfer denied for reason: %s', $reason)
        );
    }

    public static function unmappedReason(string $reason): self
    {
        return new self(
            sprintf('Transfer denied for reason: %s', $reason)
        );
    }
}
