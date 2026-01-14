<?php

declare(strict_types=1);

namespace App\Core\Application\User;

use App\Core\Domain\Contracts\Event\Publisher;
use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\Event\PendingTransferCreated;
use App\Core\Domain\Exceptions\InvalidOperation;
use App\Core\Domain\Transfer as DomainTransfer;

class TransferHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private TransferRepository $transferRepository,
        private Publisher $publisher
    ) {
    }

    public function handle(Transfer $command): string
    {
        $payer = $this->userRepository->getOneById($command->getPayerId());
        $payee = $this->userRepository->getOneById($command->getPayeeId());

        if ($payer->getWallet()->getId() === $payee->getWallet()->getId()) {
            throw InvalidOperation::sameUser();
        }

        if ($payer->cantTransfer()) {
            throw InvalidOperation::userType('This user type cannot transfer');
        }

        $payer->getWallet()->hasEnoughFunds($command->getAmount());

        $transfer = DomainTransfer::createPending(
            $payer->getWallet()->getId(),
            $payee->getWallet()->getId(),
            $command->getAmount()
        );

        $this->transferRepository->save($transfer);
        $this->publisher->publish(new PendingTransferCreated($transfer->getId()));

        return $transfer->getId();
    }
}
