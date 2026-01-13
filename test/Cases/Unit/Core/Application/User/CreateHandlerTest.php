<?php

declare(strict_types=1);

namespace HyperfTest\Cases\User;

use App\Core\Application\User\CreateCommand;
use App\Core\Application\User\CreateHandler;
use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\User\User;
use App\Core\Domain\User\UserFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class CreateHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandleCreatesNewUser(): void
    {
        $factory = new UserFactory();
        $repository = Mockery::mock(UserRepository::class);
        $command = $this->createCommand();
        $capturedUser = null;

        $repository->shouldReceive('getOneOrNullByEmail')
            ->once()
            ->with($command->getEmail())
            ->andReturn(null);
        $repository->shouldReceive('getOneOrNullByDocument')
            ->once()
            ->with($command->getDocument())
            ->andReturn(null);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (User $user) use (&$capturedUser) {
                $capturedUser = $user;
                return true;
            }));

        $handler = new CreateHandler($repository, $factory);

        $result = $handler->handle($command);

        $this->assertInstanceOf(User::class, $capturedUser);
        $this->assertSame($capturedUser->getId(), $result);
    }

    public function testHandleThrowsWhenEmailAlreadyExists(): void
    {
        $factory = new UserFactory();
        $repository = Mockery::mock(UserRepository::class);
        $command = $this->createCommand();
        $existingUser = $this->createExistingUser($factory, ['email' => $command->getEmail()]);

        $repository->shouldReceive('getOneOrNullByEmail')
            ->once()
            ->with($command->getEmail())
            ->andReturn($existingUser);
        $repository->shouldNotReceive('getOneOrNullByDocument');
        $repository->shouldNotReceive('save');

        $handler = new CreateHandler($repository, $factory);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Email already in use');

        $handler->handle($command);
    }

    public function testHandleThrowsWhenDocumentAlreadyExists(): void
    {
        $factory = new UserFactory();
        $repository = Mockery::mock(UserRepository::class);
        $command = $this->createCommand();
        $existingUser = $this->createExistingUser($factory, ['document' => $command->getDocument()]);

        $repository->shouldReceive('getOneOrNullByEmail')
            ->once()
            ->with($command->getEmail())
            ->andReturn(null);
        $repository->shouldReceive('getOneOrNullByDocument')
            ->once()
            ->with($command->getDocument())
            ->andReturn($existingUser);
        $repository->shouldNotReceive('save');

        $handler = new CreateHandler($repository, $factory);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Document already in use');

        $handler->handle($command);
    }

    private function createCommand(array $overrides = []): CreateCommand
    {
        $data = array_merge([
            'fullName' => 'John Doe',
            'kind' => UserKind::common->value,
            'documentType' => DocumentType::cpf->value,
            'document' => '99999999993',
            'email' => 'john.doe@example.com',
            'password' => 'strong-password',
        ], $overrides);

        return new CreateCommand(
            $data['fullName'],
            $data['kind'],
            $data['documentType'],
            $data['document'],
            $data['email'],
            $data['password'],
        );
    }

    private function createExistingUser(UserFactory $factory, array $overrides = []): User
    {
        $data = array_merge([
            'id' => 'existing-user-id',
            'fullName' => 'Jane Roe',
            'kind' => UserKind::common->value,
            'documentType' => DocumentType::cpf->value,
            'document' => '11122233344',
            'email' => 'existing@example.com',
            'password' => 'password',
        ], $overrides);

        return $factory->create(
            fullName: $data['fullName'],
            kind: $data['kind'],
            documentType: $data['documentType'],
            document: $data['document'],
            email: $data['email'],
            password: $data['password'],
            id: $data['id'],
        );
    }
}
