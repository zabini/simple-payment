<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Application\User;

use App\Core\Application\User\Deposit;
use App\Core\Application\User\DepositHandler;
use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\Contracts\WalletRepository;
use App\Core\Domain\User\UserFactory;
use App\Core\Domain\Wallet;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DepositHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandleDepositsAndPersistsWallet(): void
    {
        $factory = new UserFactory();
        $wallet = Wallet::create(userId: 'user-1', id: 'wallet-1');
        $user = $factory->create(
            fullName: 'John Doe',
            kind: UserKind::common->value,
            documentType: DocumentType::cpf->value,
            document: '12345678900',
            email: 'john@example.com',
            password: 'secret',
            id: 'user-1',
            wallet: $wallet,
        );

        $userRepository = Mockery::mock(UserRepository::class);
        $walletRepository = Mockery::mock(WalletRepository::class);
        $command = new Deposit('user-1', 5.0);
        $savedWallet = null;

        $userRepository->shouldReceive('getOneById')
            ->once()
            ->with($command->getUserId())
            ->andReturn($user);

        $walletRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Wallet $persisted) use (&$savedWallet) {
                $savedWallet = $persisted;
                return true;
            }));

        $handler = new DepositHandler($userRepository, $walletRepository);

        $handler->handle($command);

        assert($savedWallet instanceof Wallet);

        $this->assertSame($wallet, $savedWallet);
        $this->assertSame(5.0, $savedWallet->getBalance());
    }

    public function testHandleDoesNotPersistWhenDepositFails(): void
    {
        $factory = new UserFactory();
        $user = $factory->create(
            fullName: 'John Doe',
            kind: UserKind::common->value,
            documentType: DocumentType::cpf->value,
            document: '12345678900',
            email: 'john@example.com',
            password: 'secret',
            id: 'user-1',
        );

        $userRepository = Mockery::mock(UserRepository::class);
        $walletRepository = Mockery::mock(WalletRepository::class);
        $command = new Deposit('user-1', -5.0);

        $userRepository->shouldReceive('getOneById')
            ->once()
            ->with($command->getUserId())
            ->andReturn($user);
        $walletRepository->shouldNotReceive('save');

        $handler = new DepositHandler($userRepository, $walletRepository);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than zero');

        $handler->handle($command);
    }
}
