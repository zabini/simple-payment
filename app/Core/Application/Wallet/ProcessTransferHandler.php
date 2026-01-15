<?php

declare(strict_types=1);

namespace App\Core\Application\Wallet;

use App\Core\Domain\Contracts\Event\Publisher;
use App\Core\Domain\Contracts\TransferAuthorizer;
use App\Core\Domain\Contracts\TransferRepository;
use App\Core\Domain\Contracts\WalletRepository;
use App\Core\Domain\Event\Transfer\Completed as CompletedTransfer;
use App\Core\Domain\Exceptions\InvalidOperation;
use Hyperf\Di\Annotation\Inject;
use Throwable;

class ProcessTransferHandler
{
    #[Inject(lazy: true)]
    private Publisher $publisher;

    public function __construct(
        private TransferRepository $transferRepository,
        private WalletRepository $walletRepository,
        private TransferAuthorizer $transferAuthorizer
    ) {
    }

    public function handle(ProcessTransfer $command)
    {
        $transfer = $this->transferRepository->getOneById($command->getTransferId());

        if ($transfer->isntProcessable()) {
            throw InvalidOperation::unprocessableTransfer();
        }

        $payerWallet = $transfer->getPayerWallet();
        $payeeWallet = $transfer->getPayeeWallet();

        try {
            $this->transferAuthorizer
                ->authorize($payerWallet->getUserId());
        } catch (Throwable $exception) {
            $transfer->fail($exception->getMessage() ?: 'External authorization failed');
            $this->transferRepository->save($transfer);
            throw $exception;
        }

        $payerWallet->transferTo($payeeWallet, $transfer);

        $this->walletRepository->save($payerWallet);
        $this->walletRepository->save($payeeWallet);

        $transfer->complete();

        $this->transferRepository->save($transfer);

        $this->publisher->publish(
            new CompletedTransfer($transfer->getId())
        );
    }
}
