<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain\Exceptions;

use App\Core\Domain\Exceptions\InvalidOperation;
use App\Core\Domain\Exceptions\InvalidUser;
use App\Core\Domain\Exceptions\NotFound;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Core\Domain\Exceptions\DomainException
 * @covers \App\Core\Domain\Exceptions\InvalidOperation
 * @covers \App\Core\Domain\Exceptions\InvalidUser
 * @covers \App\Core\Domain\Exceptions\NotFound
 * @internal
 */
class DomainExceptionsTest extends TestCase
{
    /**
     * @dataProvider invalidOperationProvider
     */
    public function testInvalidOperationFactories(callable $factory, string $expectedMessage): void
    {
        $exception = $factory();

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame('BUSINESS_ERROR', $exception->getErrorCode());
        $this->assertSame(422, $exception->getStatusCode());
        $this->assertSame([], $exception->getErrors());
    }

    public static function invalidOperationProvider(): array
    {
        return [
            'same user' => [static fn () => InvalidOperation::sameUser(), 'Payer and Payee must differ'],
            'user type' => [static fn () => InvalidOperation::userType(), 'User type is not able to transfer money'],
            'zeroed amount' => [static fn () => InvalidOperation::zeroedAmount(), 'Amount must be greater than zero'],
            'not enough funds' => [static fn () => InvalidOperation::noEnoughFunds(), 'No enough funds'],
            'unprocessable transfer' => [static fn () => InvalidOperation::unprocessableTransfer(), 'Transfer is not processable'],
            'external reason' => [static fn () => InvalidOperation::fromExternalReason('Authorization failed'), 'Transfer denied for reason: Authorization failed'],
            'unmapped reason' => [static fn () => InvalidOperation::unmappedReason('Unexpected error'), 'Transfer denied for reason: Unexpected error'],
        ];
    }

    /**
     * @dataProvider invalidUserProvider
     */
    public function testInvalidUserFactories(callable $factory, string $expectedMessage): void
    {
        $exception = $factory();

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame('BUSINESS_ERROR', $exception->getErrorCode());
        $this->assertSame(422, $exception->getStatusCode());
        $this->assertSame([], $exception->getErrors());
    }

    public static function invalidUserProvider(): array
    {
        return [
            'email already taken' => [static fn () => InvalidUser::emailAlreadyTaken('john@example.com'), 'Provided user email already been taken  john@example.com'],
            'invalid type' => [static fn () => InvalidUser::invalidUserType('ghost'), 'Provided user type (ghost) is invalid'],
            'document in use' => [static fn () => InvalidUser::documentInUse('12345678900'), 'Provided document (12345678900) already in use'],
            'invalid document type' => [static fn () => InvalidUser::invalidDocumentType('passport'), 'Provided document type (passport) is invalid'],
        ];
    }

    public function testNotFoundFactory(): void
    {
        $exception = NotFound::entityWithId('User', 'user-1');

        $this->assertSame('Provided User id (user-1) was not found', $exception->getMessage());
        $this->assertSame('BUSINESS_ERROR', $exception->getErrorCode());
        $this->assertSame(422, $exception->getStatusCode());
        $this->assertSame([], $exception->getErrors());
    }
}
