<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Exceptions\InvalidOperation;
use App\Core\Domain\LedgerEntry;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
class LedgerEntryTest extends TestCase
{
    public function testCreateBuildsCreditEntry(): void
    {
        $entry = LedgerEntry::create(
            walletId: 'wallet-1',
            amount: 10.5,
            type: LedgerEntryType::credit,
            operation: LedgerOperation::manual,
            id: 'entry-1',
            transferId: 'transfer-1'
        );

        $this->assertSame('entry-1', $entry->getId());
        $this->assertSame('wallet-1', $entry->getWalletId());
        $this->assertSame(10.5, $entry->getAmount());
        $this->assertSame(LedgerEntryType::credit, $entry->getType());
        $this->assertSame(LedgerOperation::manual, $entry->getOperation());
        $this->assertSame('transfer-1', $entry->getTransferId());
        $this->assertTrue($entry->isCredit());
        $this->assertFalse($entry->isDebit());
    }

    public function testCreateGeneratesIdsWhenNotProvided(): void
    {
        $entry = LedgerEntry::create(
            walletId: 'wallet-1',
            amount: 5.0,
            type: LedgerEntryType::debit,
            operation: LedgerOperation::transfer
        );

        $this->assertTrue(Uuid::isValid($entry->getId()));
        $this->assertTrue($entry->isDebit());
        $this->assertFalse($entry->isCredit());
    }

    public function testZeroOrNegativeAmountThrows(): void
    {
        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Amount must be greater than zero');

        LedgerEntry::create(
            walletId: 'wallet-1',
            amount: 0.0,
            type: LedgerEntryType::credit,
            operation: LedgerOperation::manual
        );
    }
}
