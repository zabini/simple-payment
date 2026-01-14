<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Application\Wallet;

use App\Core\Application\Wallet\ProcessTransfer;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Contracts\WalletRepository;
use App\Core\Domain\Transfer;
use App\Core\Domain\Wallet;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

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

        $payerWallet = Wallet::create('payer-1', 'payer-wallet-1');
        $payerWallet->deposit(100);

        $payeeWallet = Wallet::create('payee-1', 'payee-wallet-1');

        $transfer = Transfer::createPending(
            payerWalletId: 'payer-wallet-1',
            payeeWalletId: 'payee-wallet-1',
            amount: 50.0,
            id: 'transfer-1'
        );

        $transferRepository->shouldReceive('getOneById')
            ->once()
            ->with('transfer-1')
            ->andReturn($transfer);

        $walletRepository->shouldReceive('getOneById')
            ->once()
            ->with('payer-wallet-1')
            ->andReturn($payerWallet);

        $walletRepository->shouldReceive('getOneById')
            ->once()
            ->with('payee-wallet-1')
            ->andReturn($payeeWallet);

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

        $handler = new ProcessTransferHandler($transferRepository, $walletRepository);

        $handler->handle(new ProcessTransfer('transfer-1'));
    }
}
