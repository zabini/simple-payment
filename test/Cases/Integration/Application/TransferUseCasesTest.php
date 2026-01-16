<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Integration\Application;

use App\Core\Application\Transfer\NotifyPayee;
use App\Core\Application\Transfer\NotifyPayeeHandler;
use App\Core\Application\User\Transfer as CreateTransfer;
use App\Core\Application\User\TransferHandler;
use App\Core\Application\Wallet\ProcessTransfer;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Event\Transfer\Completed as CompletedEvent;
use App\Core\Domain\Exceptions\InvalidOperation;
use HyperfTest\Integration\Application\ApplicationIntegrationTestCase;

/**
 * @internal
 */
class TransferUseCasesTest extends ApplicationIntegrationTestCase
{
    public function testProcessTransferMovesFundsAndCompletesTransfer(): void
    {
        $initialBalance = 300.0;
        $transferAmount = 125.5;

        [$transferId, $payerId, $payeeId] = $this->createPendingTransfer($initialBalance, $transferAmount);

        $handler = $this->makeProcessTransferHandler();
        $handler->handle(new ProcessTransfer($transferId));

        $transfer = $this->transferRepository->getOneById($transferId);
        $this->assertSame(TransferStatus::completed, $transfer->getStatus());
        $this->assertNull($transfer->getFailedReason());

        $payer = $this->userRepository->getOneById($payerId);
        $payee = $this->userRepository->getOneById($payeeId);

        $this->assertSame($initialBalance - $transferAmount, $payer->getWallet()->getBalance());
        $this->assertSame($transferAmount, $payee->getWallet()->getBalance());

        $events = $this->publisher->releasedEvents();
        $this->assertInstanceOf(CompletedEvent::class, $events[array_key_last($events)]);
        $this->assertSame([$payerId], $this->transferAuthorizer->authorized());
    }

    public function testProcessTransferFailsWhenAuthorizationDenied(): void
    {
        $initialBalance = 200.0;
        $transferAmount = 75.0;

        [$transferId, $payerId, $payeeId] = $this->createPendingTransfer($initialBalance, $transferAmount);
        $this->transferAuthorizer->denyWith('External denial');

        $handler = $this->makeProcessTransferHandler();

        $this->expectException(InvalidOperation::class);
        $handler->handle(new ProcessTransfer($transferId));

        $transfer = $this->transferRepository->getOneById($transferId);
        $this->assertSame(TransferStatus::failed, $transfer->getStatus());
        $this->assertSame('Transfer denied for reason: External denial', $transfer->getFailedReason());

        $payer = $this->userRepository->getOneById($payerId);
        $payee = $this->userRepository->getOneById($payeeId);

        $this->assertSame($initialBalance, $payer->getWallet()->getBalance());
        $this->assertSame(0.0, $payee->getWallet()->getBalance());

        $events = $this->publisher->releasedEvents();
        $this->assertCount(1, $events); // only pending creation was published
    }

    public function testNotifyPayeeUsesNotifier(): void
    {
        [$transferId,, $payeeId] = $this->createPendingTransfer(150.0, 50.0);

        $handler = new NotifyPayeeHandler($this->transferRepository, $this->notifier);
        $handler->handle(new NotifyPayee($transferId));

        $this->assertSame([$payeeId], $this->notifier->notifiedUsers());
    }

    private function createPendingTransfer(float $depositAmount, float $transferAmount): array
    {
        $payerId = $this->createUser();
        $payeeId = $this->createUser();

        $this->deposit($payerId, $depositAmount);

        $handler = $this->makeTransferHandler();
        $transferId = $handler->handle(new CreateTransfer($payerId, $payeeId, $transferAmount));

        return [$transferId, $payerId, $payeeId];
    }
}
