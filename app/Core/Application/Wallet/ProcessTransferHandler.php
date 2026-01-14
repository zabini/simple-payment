<?php

declare(strict_types=1);

namespace App\Core\Application\Wallet;

use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Contracts\WalletRepository;
use App\Core\Domain\Exceptions\InvalidOperation;

class ProcessTransferHandler
{
    public function __construct(
        private TransferRepository $transferRepository,
        private WalletRepository $walletRepository,
    ) {
    }

    public function handle(ProcessTransfer $command)
    {
        $transfer = $this->transferRepository->getOneById($command->getTransferId());

        if ($transfer->isntProcessable()) {
            throw InvalidOperation::unprocessableTransfer();
        }

        $payerWallet = $this->walletRepository->getOneById($transfer->getPayerWalletId());
        $payeeWallet = $this->walletRepository->getOneById($transfer->getPayeeWalletId());

        $payerWallet->transferTo($payeeWallet, $transfer->getAmount());

        $this->walletRepository->save($payerWallet);
        $this->walletRepository->save($payeeWallet);

        $transfer->complete();

        $this->transferRepository->save($transfer);
    }
}
