<?php

declare(strict_types=1);

namespace App\Core\Application\User;

use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\Contracts\WalletRepository;

class DepositHandler
{

    /**
     * @param UserRepository $repository
     * @param UserFactory $factory
     */
    public function __construct(
        private UserRepository $repository,
        private WalletRepository $walletRepository
    ) {}

    /**
     * @param DepositCommand $command
     * @return string
     */
    public function handle(DepositCommand $command): void
    {

        $wallet = $this->repository->getOneById($command->getUserId())
            ->getWallet();

        $wallet->deposit($command->getAmount());

        $this->walletRepository->save($wallet);
    }
}
