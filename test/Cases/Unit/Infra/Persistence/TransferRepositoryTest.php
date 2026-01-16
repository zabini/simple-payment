<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\Transfer as DomainTransfer;
use App\Core\Domain\Wallet as DomainWallet;
use App\Infra\ORM\LedgerEntry as ORMLedgerEntry;
use App\Infra\ORM\Transfer as ORMTransfer;
use App\Infra\ORM\Wallet as ORMWallet;
use App\Infra\Persistence\TransferRepository;
use Hyperf\Collection\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Persistence\TransferRepository
 * @internal
 */
class TransferRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSavePersistsTransfer(): void
    {
        $payer = DomainWallet::create('payer-user', 'payer-wallet');
        $payee = DomainWallet::create('payee-user', 'payee-wallet');
        $transfer = DomainTransfer::createPending($payer, $payee, 75.0, 'transfer-1');

        $repository = Mockery::mock(TransferRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $query = Mockery::mock();
        $repository->shouldReceive('transferQuery')->andReturn($query);

        $query->shouldReceive('updateOrCreate')
            ->once()
            ->with(['id' => 'transfer-1'], [
                'payer_wallet_id' => 'payer-wallet',
                'payee_wallet_id' => 'payee-wallet',
                'amount' => 75.0,
                'status' => TransferStatus::pending->value,
                'failed_reason' => null,
            ]);

        $repository->save($transfer);
    }

    public function testGetOneByIdRebuildsTransferAndWallets(): void
    {
        $repository = Mockery::mock(TransferRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        [$ormTransfer, $expectedPayerBalance, $expectedPayeeBalance] = $this->buildOrmTransferFixture();

        $query = Mockery::mock();
        $repository->shouldReceive('transferQuery')->andReturn($query);

        $query->shouldReceive('with')
            ->once()
            ->with([
                'payerWallet.ledgerEntries',
                'payeeWallet.ledgerEntries',
            ])
            ->andReturnSelf();

        $query->shouldReceive('find')
            ->once()
            ->with('transfer-1')
            ->andReturn($ormTransfer);

        $transfer = $repository->getOneById('transfer-1');

        $this->assertSame('transfer-1', $transfer->getId());
        $this->assertSame(TransferStatus::pending, $transfer->getStatus());
        $this->assertSame(120.0, $transfer->getAmount());
        $this->assertNull($transfer->getFailedReason());

        $payerWallet = $transfer->getPayerWallet();
        $payeeWallet = $transfer->getPayeeWallet();

        $this->assertSame($expectedPayerBalance, $payerWallet->getBalance());
        $this->assertSame($expectedPayeeBalance, $payeeWallet->getBalance());

        $this->assertCount(2, $payerWallet->getLedgerEntries());
        $this->assertCount(1, $payeeWallet->getLedgerEntries());
        $this->assertSame('transfer-1', $payerWallet->getLedgerEntries()[1]->getTransferId());
        $this->assertSame('transfer-1', $payeeWallet->getLedgerEntries()[0]->getTransferId());
    }

    public function testGetOneByIdThrowsWhenNotFound(): void
    {
        $repository = Mockery::mock(TransferRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $query = Mockery::mock();
        $repository->shouldReceive('transferQuery')->andReturn($query);

        $query->shouldReceive('with')
            ->once()
            ->with([
                'payerWallet.ledgerEntries',
                'payeeWallet.ledgerEntries',
            ])
            ->andReturnSelf();

        $query->shouldReceive('find')
            ->once()
            ->with('missing')
            ->andReturn(null);

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('Provided transfer id (missing) was not found');

        $repository->getOneById('missing');
    }

    /**
     * @return array{0: ORMTransfer, 1: float, 2: float}
     */
    private function buildOrmTransferFixture(): array
    {
        $payerLedger = new Collection();

        $payerCredit = new ORMLedgerEntry();
        $payerCredit->setAttribute('id', 'payer-entry-1');
        $payerCredit->setAttribute('wallet_id', 'payer-wallet');
        $payerCredit->setAttribute('amount', 200.0);
        $payerCredit->setAttribute('type', LedgerEntryType::credit->value);
        $payerCredit->setAttribute('operation', LedgerOperation::manual->value);

        $payerDebit = new ORMLedgerEntry();
        $payerDebit->setAttribute('id', 'payer-entry-2');
        $payerDebit->setAttribute('wallet_id', 'payer-wallet');
        $payerDebit->setAttribute('amount', 80.0);
        $payerDebit->setAttribute('type', LedgerEntryType::debit->value);
        $payerDebit->setAttribute('operation', LedgerOperation::transfer->value);
        $payerDebit->setAttribute('transfer_id', 'transfer-1');

        $payerLedger->push($payerCredit);
        $payerLedger->push($payerDebit);

        $payeeLedger = new Collection();
        $payeeCredit = new ORMLedgerEntry();
        $payeeCredit->setAttribute('id', 'payee-entry-1');
        $payeeCredit->setAttribute('wallet_id', 'payee-wallet');
        $payeeCredit->setAttribute('amount', 80.0);
        $payeeCredit->setAttribute('type', LedgerEntryType::credit->value);
        $payeeCredit->setAttribute('operation', LedgerOperation::transfer->value);
        $payeeCredit->setAttribute('transfer_id', 'transfer-1');

        $payeeLedger->push($payeeCredit);

        $ormPayerWallet = new ORMWallet();
        $ormPayerWallet->setAttribute('id', 'payer-wallet');
        $ormPayerWallet->setAttribute('user_id', 'payer-user');
        $ormPayerWallet->ledgerEntries = $payerLedger;

        $ormPayeeWallet = new ORMWallet();
        $ormPayeeWallet->setAttribute('id', 'payee-wallet');
        $ormPayeeWallet->setAttribute('user_id', 'payee-user');
        $ormPayeeWallet->ledgerEntries = $payeeLedger;

        $ormTransfer = new ORMTransfer();
        $ormTransfer->setAttribute('id', 'transfer-1');
        $ormTransfer->setAttribute('payer_wallet_id', 'payer-wallet');
        $ormTransfer->setAttribute('payee_wallet_id', 'payee-wallet');
        $ormTransfer->setAttribute('amount', 120.0);
        $ormTransfer->setAttribute('status', TransferStatus::pending->value);
        $ormTransfer->setAttribute('failed_reason', null);
        $ormTransfer->payerWallet = $ormPayerWallet;
        $ormTransfer->payeeWallet = $ormPayeeWallet;

        $expectedPayerBalance = 120.0;
        $expectedPayeeBalance = 80.0;

        return [$ormTransfer, $expectedPayerBalance, $expectedPayeeBalance];
    }
}
