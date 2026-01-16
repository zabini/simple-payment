<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Integration\Application;

use App\Core\Application\User\Deposit;
use App\Core\Application\User\DepositHandler;
use App\Core\Application\User\FetchById;
use App\Core\Application\User\FetchByIdHandler;
use App\Core\Application\User\Transfer;
use App\Core\Application\User\TransferHandler;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Event\Transfer\PendingCreated;
use HyperfTest\Integration\Application\ApplicationIntegrationTestCase;

/**
 * @internal
 */
class UserUseCasesTest extends ApplicationIntegrationTestCase
{
    public function testCreateAndFetchUser(): void
    {
        $userId = $this->createUser(['full_name' => 'John Doe']);

        $user = $this->fetchUser($userId);
        $this->assertSame('John Doe', $user->getFullName());
        $fetched = $this->fetchUser($userId);

        $this->assertSame($userId, $fetched->getId());
        $this->assertSame($user->getWallet()->getId(), $fetched->getWallet()->getId());
        $this->assertSame($user->getEmail(), $fetched->getEmail());
    }

    public function testDepositUpdatesWalletBalance(): void
    {
        $userId = $this->createUser();
        $amount = 150.75;

        $handler = new DepositHandler($this->userRepository, $this->walletRepository);
        $handler->handle(new Deposit($userId, $amount));

        $user = $this->userRepository->getOneById($userId);
        $this->assertSame($amount, $user->getWallet()->getBalance());

        $wallet = $this->walletRepository->getOneById($user->getWallet()->getId());
        $this->assertSame($amount, $wallet->getBalance());
    }

    public function testTransferCreationPersistsPendingTransferAndPublishesEvent(): void
    {
        $payerId = $this->createUser([
            'kind' => UserKind::common->value,
        ]);
        $payeeId = $this->createUser([
            'kind' => UserKind::seller->value,
        ]);
        $this->deposit($payerId, 500.0);

        $handler = $this->makeTransferHandler();
        $transferId = $handler->handle(new Transfer($payerId, $payeeId, 200.0));

        $transfer = $this->transferRepository->getOneById($transferId);
        $this->assertSame(200.0, $transfer->getAmount());
        $this->assertSame(TransferStatus::pending, $transfer->getStatus());
        $this->assertSame($payerId, $transfer->getPayerWallet()->getUserId());
        $this->assertSame($payeeId, $transfer->getPayeeWallet()->getUserId());

        $events = $this->publisher->releasedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PendingCreated::class, $events[0]);
        $this->assertSame($transferId, $events[0]->getTransferId());
    }
}
