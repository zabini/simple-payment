<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain;

use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Exceptions\InvalidOperation;
use App\Core\Domain\Transfer;
use App\Core\Domain\Wallet;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @covers \App\Core\Domain\Transfer
 * @internal
 */
class TransferTest extends TestCase
{
    public function testCreatePendingSetsPendingStatusAndAmount(): void
    {
        $payer = Wallet::create('payer', 'payer-wallet');
        $payee = Wallet::create('payee', 'payee-wallet');

        $transfer = Transfer::createPending($payer, $payee, 75.0, 'transfer-1');

        $this->assertSame('transfer-1', $transfer->getId());
        $this->assertSame($payer, $transfer->getPayerWallet());
        $this->assertSame($payee, $transfer->getPayeeWallet());
        $this->assertSame(75.0, $transfer->getAmount());
        $this->assertSame(TransferStatus::pending, $transfer->getStatus());
        $this->assertNull($transfer->getFailedReason());
    }

    public function testCreatePendingGeneratesId(): void
    {
        $transfer = Transfer::createPending(
            Wallet::create('payer', 'payer-wallet'),
            Wallet::create('payee', 'payee-wallet'),
            10.0
        );

        $this->assertTrue(Uuid::isValid($transfer->getId()));
    }

    public function testCompleteClearsFailureAndSetsStatus(): void
    {
        $transfer = Transfer::createPending(
            Wallet::create('payer', 'payer-wallet'),
            Wallet::create('payee', 'payee-wallet'),
            10.0
        );

        $transfer->fail('temporary');
        $transfer->complete();

        $this->assertSame(TransferStatus::completed, $transfer->getStatus());
        $this->assertNull($transfer->getFailedReason());
    }

    public function testFailStoresReasonAndMarksFailed(): void
    {
        $transfer = Transfer::createPending(
            Wallet::create('payer', 'payer-wallet'),
            Wallet::create('payee', 'payee-wallet'),
            10.0
        );

        $transfer->fail('denied');

        $this->assertSame(TransferStatus::failed, $transfer->getStatus());
        $this->assertSame('denied', $transfer->getFailedReason());
        $this->assertTrue($transfer->isntProcessable());
    }

    public function testPendingTransferIsProcessable(): void
    {
        $transfer = Transfer::createPending(
            Wallet::create('payer', 'payer-wallet'),
            Wallet::create('payee', 'payee-wallet'),
            10.0
        );

        $this->assertFalse($transfer->isntProcessable());
    }

    public function testZeroAmountIsRejected(): void
    {
        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Amount must be greater than zero');

        Transfer::createPending(
            Wallet::create('payer', 'payer-wallet'),
            Wallet::create('payee', 'payee-wallet'),
            0.0
        );
    }
}
