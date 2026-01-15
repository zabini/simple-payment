<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Application\Transfer;

use App\Core\Application\Transfer\NotifyPayee;
use App\Core\Application\Transfer\NotifyPayeeHandler;
use App\Core\Domain\Contracts\Notifier;
use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Transfer;
use App\Core\Domain\Wallet;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Core\Application\Transfer\NotifyPayeeHandler
 * @internal
 */
class NotifyPayeeHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testNotifiesPayeeOfTransfer(): void
    {
        $transferRepository = Mockery::mock(TransferRepository::class);
        $notifier = Mockery::mock(Notifier::class);

        $payerWallet = Wallet::create('payer-1', 'payer-wallet-1');
        $payeeWallet = Wallet::create('payee-1', 'payee-wallet-1');

        $transfer = Transfer::createPending(
            payerWallet: $payerWallet,
            payeeWallet: $payeeWallet,
            amount: 25.0,
            id: 'transfer-1',
        );

        $transferRepository->shouldReceive('getOneById')
            ->once()
            ->with('transfer-1')
            ->andReturn($transfer);

        $notifier->shouldReceive('notify')
            ->once()
            ->with('payee-1')
            ->andReturnNull();

        $handler = new NotifyPayeeHandler($transferRepository, $notifier);

        $handler->handle(new NotifyPayee('transfer-1'));
    }
}
