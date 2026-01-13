<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Application\User;

use App\Core\Application\User\FetchById;
use App\Core\Application\User\FetchByIdHandler;
use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\Exceptions\UserNotFound;
use App\Core\Domain\User\User;
use App\Core\Domain\User\UserFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Core\Application\User\FetchByIdHandler
 * @internal
 */
class FetchByIdHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandleReturnsUser(): void
    {
        $factory = new UserFactory();
        $repository = Mockery::mock(UserRepository::class);
        $command = new FetchById('user-123');
        $expectedUser = $this->makeUser($factory, 'user-123');

        $repository->shouldReceive('getOneById')
            ->once()
            ->with($command->getId())
            ->andReturn($expectedUser);

        $handler = new FetchByIdHandler($repository);

        $user = $handler->handle($command);

        $this->assertSame($expectedUser, $user);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testHandleThrowsWhenUserNotFound(): void
    {
        $repository = Mockery::mock(UserRepository::class);
        $command = new FetchById('missing-id');

        $repository->shouldReceive('getOneById')
            ->once()
            ->with($command->getId())
            ->andThrow(UserNotFound::withId($command->getId()));

        $handler = new FetchByIdHandler($repository);

        $this->expectException(UserNotFound::class);
        $this->expectExceptionMessage('Provided user id (missing-id) was not found');

        $handler->handle($command);
    }

    private function makeUser(UserFactory $factory, string $id): User
    {
        return $factory->create(
            fullName: 'Jane Roe',
            kind: UserKind::common->value,
            documentType: DocumentType::cpf->value,
            document: '11122233344',
            email: 'jane@example.com',
            password: 'secret',
            id: $id,
        );
    }
}
