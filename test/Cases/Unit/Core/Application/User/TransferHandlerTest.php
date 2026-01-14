<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Application\User;

use App\Core\Application\User\Transfer;
use App\Core\Application\User\TransferHandler;
use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\Event\Publisher;
use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\Event\PendingTransferCreated;
use App\Core\Domain\Transfer as DomainTransfer;
use App\Core\Domain\User\Common;
use App\Core\Domain\User\Seller;
use App\Core\Domain\Wallet;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @covers \App\Core\Application\User\TransferHandler
 * @internal
 */
class TransferHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCreatesPendingTransferAndPublishesEvent(): void
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $transferRepository = Mockery::mock(TransferRepository::class);
        $publisher = Mockery::mock(Publisher::class);

        $payerWallet = Wallet::create('payer-1', 'payer-wallet-1');
        $payerWallet->deposit(100);
        $payer = Common::make(
            'payer-1',
            'Payer Name',
            DocumentType::cpf,
            '12345678901',
            'payer@example.com',
            'secret',
            $payerWallet
        );

        $payeeWallet = Wallet::create('payee-1', 'payee-wallet-1');
        $payee = Seller::make(
            'payee-1',
            'Payee Name',
            DocumentType::cnpj,
            '12345678000199',
            'payee@example.com',
            'secret',
            $payeeWallet
        );

        $capturedTransfer = null;

        $userRepository->shouldReceive('getOneById')
            ->once()
            ->with('payer-1')
            ->andReturn($payer);

        $userRepository->shouldReceive('getOneById')
            ->once()
            ->with('payee-1')
            ->andReturn($payee);

        $transferRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function ($transfer) use (&$capturedTransfer) {
                if (! $transfer instanceof DomainTransfer) {
                    return false;
                }

                $capturedTransfer = $transfer;

                return $transfer->getStatus() === TransferStatus::pending
                    && $transfer->getPayerWalletId() === 'payer-wallet-1'
                    && $transfer->getPayeeWalletId() === 'payee-wallet-1'
                    && $transfer->getAmount() === 50.0;
            }))
            ->andReturnNull();

        $publisher->shouldReceive('publish')
            ->once()
            ->with(Mockery::on(function ($event) use (&$capturedTransfer) {
                return $event instanceof PendingTransferCreated
                    && $capturedTransfer !== null
                    && $event->getTransferId() === $capturedTransfer->getId();
            }))
            ->andReturnNull();

        $handler = new TransferHandler($userRepository, $transferRepository, $publisher);

        $command = new Transfer('payer-1', 'payee-1', 50.0);
        $transferId = $handler->handle($command);

        $this->assertNotNull($capturedTransfer);
        $this->assertTrue(Uuid::isValid($transferId));
    }
}
