<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Application\Wallet;

use App\Core\Application\Wallet\ProcessTransfer;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\TransferAuthorizer;
use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Contracts\WalletRepository;
use App\Core\Domain\Event\Transfer\Completed as TransferCompleted;
use App\Core\Domain\Transfer;
use App\Core\Domain\Wallet;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \App\Core\Application\Wallet\ProcessTransferHandler
 * @internal
 */
class ProcessTransferHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testProcessesPendingTransferAndPersistsChanges(): void
    {
        $transferRepository = Mockery::mock(TransferRepository::class);
        $walletRepository = Mockery::mock(WalletRepository::class);
        $transferAuthorizer = Mockery::mock(TransferAuthorizer::class);

        $payerWallet = Wallet::create('payer-1', 'payer-wallet-1');
        $payerWallet->deposit(100);

        $payeeWallet = Wallet::create('payee-1', 'payee-wallet-1');

        $transfer = Transfer::createPending(
            payerWallet: $payerWallet,
            payeeWallet: $payeeWallet,
            amount: 50.0,
            id: 'transfer-1'
        );

        $transferRepository->shouldReceive('getOneById')
            ->once()
            ->with('transfer-1')
            ->andReturn($transfer);

        $transferAuthorizer->shouldReceive('authorize')
            ->once()
            ->with($transfer->getPayerWallet()->getUserId())
            ->andReturnNull();

        $walletRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Wallet $wallet) {
                return $wallet->getId() === 'payer-wallet-1'
                    && $wallet->getBalance() === 50.0;
            }))
            ->andReturnNull();

        $walletRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Wallet $wallet) {
                return $wallet->getId() === 'payee-wallet-1'
                    && $wallet->getBalance() === 50.0;
            }))
            ->andReturnNull();

        $transferRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Transfer $transfer) {
                return $transfer->getStatus() === TransferStatus::completed
                    && $transfer->getFailedReason() === null;
            }))
            ->andReturnNull();

        $handler = new ProcessTransferHandler($transferRepository, $walletRepository, $transferAuthorizer);

        $handler->handle(new ProcessTransfer('transfer-1'));
    }

    public function testPublishesCompletedEventAfterSuccessfulProcessing(): void
    {
        $transferRepository = Mockery::mock(TransferRepository::class);
        $walletRepository = Mockery::mock(WalletRepository::class);
        $transferAuthorizer = Mockery::mock(TransferAuthorizer::class);
        $publisher = Mockery::mock(\App\Core\Domain\Contracts\Event\Publisher::class);

        $payerWallet = Wallet::create('payer-1', 'payer-wallet-1');
        $payerWallet->deposit(200);
        $payeeWallet = Wallet::create('payee-1', 'payee-wallet-1');

        $transfer = Transfer::createPending(
            payerWallet: $payerWallet,
            payeeWallet: $payeeWallet,
            amount: 75.0,
            id: 'transfer-2'
        );

        $transferRepository->shouldReceive('getOneById')
            ->once()
            ->with('transfer-2')
            ->andReturn($transfer);

        $transferAuthorizer->shouldReceive('authorize')
            ->once()
            ->with('payer-1')
            ->andReturnNull();

        $walletRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Wallet $wallet) {
                return $wallet->getId() === 'payer-wallet-1'
                    && $wallet->getBalance() === 125.0;
            }))
            ->andReturnNull();

        $walletRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Wallet $wallet) {
                return $wallet->getId() === 'payee-wallet-1'
                    && $wallet->getBalance() === 75.0;
            }))
            ->andReturnNull();

        $transferRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Transfer $savedTransfer) {
                return $savedTransfer->getStatus() === TransferStatus::completed
                    && $savedTransfer->getFailedReason() === null;
            }))
            ->andReturnNull();

        $publisher->shouldReceive('publish')
            ->once()
            ->with(Mockery::type(TransferCompleted::class))
            ->andReturnNull();

        $handler = new ProcessTransferHandler($transferRepository, $walletRepository, $transferAuthorizer);

        $publisherProperty = new ReflectionProperty($handler, 'publisher');
        $publisherProperty->setAccessible(true);
        $publisherProperty->setValue($handler, $publisher);

        $handler->handle(new ProcessTransfer('transfer-2'));
    }
}
