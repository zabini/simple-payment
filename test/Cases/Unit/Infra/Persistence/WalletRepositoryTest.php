<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\LedgerEntry as DomainLedgerEntry;
use App\Core\Domain\Wallet as DomainWallet;
use App\Infra\ORM\LedgerEntry as ORMLedgerEntry;
use App\Infra\ORM\Wallet as ORMWallet;
use App\Infra\Persistence\WalletRepository;
use Hyperf\Collection\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Persistence\WalletRepository
 * @internal
 */
class WalletRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSavePersistsOnlyNewLedgerEntries(): void
    {
        $existingEntry = DomainLedgerEntry::create(
            walletId: 'wallet-1',
            amount: 50.0,
            type: LedgerEntryType::credit,
            operation: LedgerOperation::manual,
            id: 'entry-existing'
        );

        $newEntry = DomainLedgerEntry::create(
            walletId: 'wallet-1',
            amount: 20.0,
            type: LedgerEntryType::debit,
            operation: LedgerOperation::transfer,
            id: 'entry-new',
            transferId: 'transfer-1'
        );

        $wallet = DomainWallet::create(
            userId: 'user-1',
            id: 'wallet-1',
            ledgerEntries: [$existingEntry, $newEntry]
        );

        $repository = Mockery::mock(WalletRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $ledgerQuery = Mockery::mock();
        $repository->shouldReceive('ledgerEntryQuery')
            ->times(2)
            ->andReturn($ledgerQuery);

        $ledgerQuery->shouldReceive('find')
            ->once()
            ->with('entry-existing')
            ->andReturn(new ORMLedgerEntry());

        $ledgerQuery->shouldReceive('find')
            ->once()
            ->with('entry-new')
            ->andReturn(null);

        $capturedPayload = null;
        $ormLedger = Mockery::mock(ORMLedgerEntry::class);
        $repository->shouldReceive('newOrmLedgerEntry')
            ->once()
            ->with(Mockery::on(function (array $payload) use (&$capturedPayload) {
                $capturedPayload = $payload;

                return true;
            }))
            ->andReturn($ormLedger);

        $ormLedger->shouldReceive('save')->once();

        $repository->save($wallet);

        $this->assertSame([
            'id' => 'entry-new',
            'wallet_id' => 'wallet-1',
            'amount' => 20.0,
            'type' => LedgerEntryType::debit->value,
            'operation' => LedgerOperation::transfer->value,
            'transfer_id' => 'transfer-1',
        ], $capturedPayload);
    }

    public function testGetOneByIdRebuildsWallet(): void
    {
        $repository = Mockery::mock(WalletRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository->shouldReceive('transferQuery')
            ->andReturn(
                Mockery::mock()->shouldReceive('where')->with('payer_wallet_id', 'wallet-1')->andReturnSelf()
                    ->shouldReceive('where')->with('status', TransferStatus::pending->value)->andReturnSelf()
                    ->shouldReceive('sum')->with('amount')->andReturn(25.0)
                    ->getMock()
            );

        [$ormWallet, $expectedBalance] = $this->buildOrmWalletFixture();

        $walletQuery = Mockery::mock();
        $repository->shouldReceive('walletQuery')->andReturn($walletQuery);

        $walletQuery->shouldReceive('find')
            ->once()
            ->with('wallet-1')
            ->andReturn($ormWallet);

        $wallet = $repository->getOneById('wallet-1');

        $this->assertSame('wallet-1', $wallet->getId());
        $this->assertSame('user-1', $wallet->getUserId());
        $this->assertSame($expectedBalance, $wallet->getBalance());

        $entries = $wallet->getLedgerEntries();
        $this->assertCount(2, $entries);
        $this->assertSame(LedgerEntryType::credit, $entries[0]->getType());
        $this->assertSame(LedgerEntryType::debit, $entries[1]->getType());
        $this->assertSame('transfer-2', $entries[1]->getTransferId());
    }

    public function testGetOneByIdThrowsWhenNotFound(): void
    {
        $repository = Mockery::mock(WalletRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $walletQuery = Mockery::mock();
        $repository->shouldReceive('walletQuery')->andReturn($walletQuery);

        $walletQuery->shouldReceive('find')
            ->once()
            ->with('missing')
            ->andReturn(null);

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('Provided wallet id (missing) was not found');

        $repository->getOneById('missing');
    }

    /**
     * @return array{0: ORMWallet, 1: float}
     */
    private function buildOrmWalletFixture(): array
    {
        $ledgerEntries = new Collection();

        $credit = new ORMLedgerEntry();
        $credit->setAttribute('id', 'entry-1');
        $credit->setAttribute('wallet_id', 'wallet-1');
        $credit->setAttribute('amount', 100.0);
        $credit->setAttribute('type', LedgerEntryType::credit->value);
        $credit->setAttribute('operation', LedgerOperation::manual->value);

        $debit = new ORMLedgerEntry();
        $debit->setAttribute('id', 'entry-2');
        $debit->setAttribute('wallet_id', 'wallet-1');
        $debit->setAttribute('amount', 40.0);
        $debit->setAttribute('type', LedgerEntryType::debit->value);
        $debit->setAttribute('operation', LedgerOperation::transfer->value);
        $debit->setAttribute('transfer_id', 'transfer-2');

        $ledgerEntries->push($credit);
        $ledgerEntries->push($debit);

        $ormWallet = new ORMWallet();
        $ormWallet->setAttribute('id', 'wallet-1');
        $ormWallet->setAttribute('user_id', 'user-1');
        $ormWallet->ledgerEntries = $ledgerEntries;

        $expectedBalance = 60.0;

        return [$ormWallet, $expectedBalance];
    }
}
