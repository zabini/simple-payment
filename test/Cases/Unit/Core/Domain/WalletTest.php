<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Exceptions\InvalidOperation;
use App\Core\Domain\Transfer;
use App\Core\Domain\Wallet;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class WalletTest extends TestCase
{
    public function testDepositAppendsCreditEntryAndUpdatesBalance(): void
    {
        $wallet = Wallet::create('user-1', 'wallet-1');

        $wallet->deposit(150.5);

        $entries = $wallet->getLedgerEntries();
        $this->assertCount(1, $entries);
        $entry = $entries[0];

        $this->assertSame(150.5, $wallet->getBalance());
        $this->assertSame(LedgerEntryType::credit, $entry->getType());
        $this->assertSame(LedgerOperation::manual, $entry->getOperation());
        $this->assertSame(150.5, $entry->getAmount());
        $this->assertNull($entry->getTransferId());
    }

    public function testHasEnoughFundsRespectsCommittedBalance(): void
    {
        $wallet = Wallet::create(
            userId: 'user-1',
            id: 'wallet-1',
            committedBalance: 30.0
        );
        $wallet->deposit(50.0);

        $this->assertTrue($wallet->hasEnoughFunds(20.0));

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('No enough funds');
        $wallet->hasEnoughFunds(25.0);
    }

    public function testTransferToCreatesDebitAndCreditEntries(): void
    {
        $payerWallet = Wallet::create('payer', 'payer-wallet');
        $payeeWallet = Wallet::create('payee', 'payee-wallet');
        $payerWallet->deposit(100.0);

        $transfer = Transfer::createPending($payerWallet, $payeeWallet, 40.0, 'transfer-123');

        $payerWallet->transferTo($payeeWallet, $transfer);

        $this->assertSame(60.0, $payerWallet->getBalance());
        $this->assertSame(40.0, $payeeWallet->getBalance());

        $payerEntries = $payerWallet->getLedgerEntries();
        $payeeEntries = $payeeWallet->getLedgerEntries();

        $this->assertCount(2, $payerEntries);
        $this->assertSame(LedgerEntryType::debit, $payerEntries[1]->getType());
        $this->assertSame(LedgerOperation::transfer, $payerEntries[1]->getOperation());
        $this->assertSame('transfer-123', $payerEntries[1]->getTransferId());

        $this->assertCount(1, $payeeEntries);
        $this->assertSame(LedgerEntryType::credit, $payeeEntries[0]->getType());
        $this->assertSame(LedgerOperation::transfer, $payeeEntries[0]->getOperation());
        $this->assertSame('transfer-123', $payeeEntries[0]->getTransferId());
    }

    public function testDepositWithZeroAmountThrows(): void
    {
        $wallet = Wallet::create('user-1', 'wallet-1');

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Amount must be greater than zero');

        $wallet->deposit(0.0);
    }
}
