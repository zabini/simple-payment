<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\User\UserFactory;
use App\Core\Domain\Wallet;
use App\Infra\ORM\User as ORMUser;
use App\Infra\ORM\Wallet as ORMWallet;
use App\Infra\Persistence\UserRepository;
use Hyperf\Collection\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Persistence\UserRepository
 * @internal
 */
class UserRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private UserFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new UserFactory();
    }

    public function testSavePersistsUserAndWalletModels(): void
    {
        $user = $this->factory->create(
            fullName: 'John Doe',
            kind: UserKind::common->value,
            documentType: DocumentType::cpf->value,
            document: '12345678900',
            email: 'john@example.com',
            password: 'hashed-password',
            id: 'user-123',
            wallet: Wallet::create('user-123', 'wallet-456')
        );

        $capturedUserPayload = null;
        $capturedWalletPayload = null;

        $ormUser = Mockery::mock(ORMUser::class);
        $ormWallet = Mockery::mock(ORMWallet::class);

        $repository = Mockery::mock(UserRepository::class, [$this->factory])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository->shouldReceive('newOrmUser')
            ->once()
            ->with(Mockery::on(function (array $attributes) use (&$capturedUserPayload) {
                $capturedUserPayload = $attributes;

                return true;
            }))
            ->andReturn($ormUser);

        $repository->shouldReceive('newOrmWallet')
            ->once()
            ->with(Mockery::on(function (array $attributes) use (&$capturedWalletPayload) {
                $capturedWalletPayload = $attributes;

                return true;
            }))
            ->andReturn($ormWallet);

        $ormUser->shouldReceive('save')->once();
        $ormWallet->shouldReceive('save')->once();

        $repository->save($user);

        $this->assertSame([
            'id' => 'user-123',
            'full_name' => 'John Doe',
            'kind' => UserKind::common->value,
            'document_type' => DocumentType::cpf->value,
            'document' => '12345678900',
            'email' => 'john@example.com',
            'password' => 'hashed-password',
        ], $capturedUserPayload);

        $this->assertSame([
            'id' => 'wallet-456',
            'user_id' => 'user-123',
        ], $capturedWalletPayload);
    }

    public function testGetOneByIdRebuildsDomainUser(): void
    {
        $repository = Mockery::mock(UserRepository::class, [$this->factory])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockTransferSum($repository, 'wallet-1', 25.0);
        [$ormUser, $expectedBalance] = $this->buildOrmUserFixture();

        $userQuery = Mockery::mock();
        $repository->shouldReceive('userQuery')->andReturn($userQuery);

        $userQuery->shouldReceive('find')
            ->once()
            ->with('user-1')
            ->andReturn($ormUser);

        $user = $repository->getOneById('user-1');

        $this->assertSame('user-1', $user->getId());
        $this->assertSame('Jane Roe', $user->getFullName());
        $this->assertSame(UserKind::common, $user->getKind());
        $this->assertSame(DocumentType::cpf, $user->getDocumentType());
        $this->assertSame('12345678900', $user->getDocument());
        $this->assertSame('jane@example.com', $user->getEmail());
        $this->assertSame('strong-password', $user->getPassword());

        $wallet = $user->getWallet();
        $this->assertSame('wallet-1', $wallet->getId());
        $this->assertSame('user-1', $wallet->getUserId());
        $this->assertSame($expectedBalance, $wallet->getBalance());

        $entries = $wallet->getLedgerEntries();
        $this->assertCount(2, $entries);

        $this->assertSame('entry-1', $entries[0]->getId());
        $this->assertSame(LedgerEntryType::credit, $entries[0]->getType());
        $this->assertSame(LedgerOperation::manual, $entries[0]->getOperation());
        $this->assertSame(200.0, $entries[0]->getAmount());
        $this->assertNull($entries[0]->getTransferId());

        $this->assertSame('entry-2', $entries[1]->getId());
        $this->assertSame(LedgerEntryType::debit, $entries[1]->getType());
        $this->assertSame(LedgerOperation::transfer, $entries[1]->getOperation());
        $this->assertSame(50.0, $entries[1]->getAmount());
        $this->assertSame('transfer-99', $entries[1]->getTransferId());
    }

    public function testGetOneByIdThrowsWhenUserIsMissing(): void
    {
        $repository = Mockery::mock(UserRepository::class, [$this->factory])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $userQuery = Mockery::mock();
        $repository->shouldReceive('userQuery')->andReturn($userQuery);

        $userQuery->shouldReceive('find')
            ->once()
            ->with('missing-id')
            ->andReturn(null);

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('Provided user id (missing-id) was not found');

        $repository->getOneById('missing-id');
    }

    public function testGetOneOrNullByEmailReturnsNullWhenNotFound(): void
    {
        $repository = Mockery::mock(UserRepository::class, [$this->factory])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $userQuery = Mockery::mock();
        $repository->shouldReceive('userQuery')->andReturn($userQuery);

        $userQuery->shouldReceive('where')
            ->once()
            ->with('email', 'ghost@example.com')
            ->andReturnSelf();

        $userQuery->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $this->assertNull($repository->getOneOrNullByEmail('ghost@example.com'));
    }

    public function testGetOneOrNullByDocumentReturnsUser(): void
    {
        $repository = Mockery::mock(UserRepository::class, [$this->factory])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockTransferSum($repository, 'wallet-1', 10.0);
        [$ormUser] = $this->buildOrmUserFixture();

        $userQuery = Mockery::mock();
        $repository->shouldReceive('userQuery')->andReturn($userQuery);

        $userQuery->shouldReceive('where')
            ->once()
            ->with('document', '12345678900')
            ->andReturnSelf();

        $userQuery->shouldReceive('first')
            ->once()
            ->andReturn($ormUser);

        $user = $repository->getOneOrNullByDocument('12345678900');

        $this->assertNotNull($user);
        $this->assertSame('jane@example.com', $user?->getEmail());
    }

    private function mockTransferSum(UserRepository $repository, string $walletId, float $sum): void
    {
        $transferQuery = Mockery::mock();
        $repository->shouldReceive('transferQuery')->andReturn($transferQuery);

        $transferQuery->shouldReceive('where')
            ->once()
            ->with('payer_wallet_id', $walletId)
            ->andReturnSelf();

        $transferQuery->shouldReceive('where')
            ->once()
            ->with('status', TransferStatus::pending->value)
            ->andReturnSelf();

        $transferQuery->shouldReceive('sum')
            ->once()
            ->with('amount')
            ->andReturn($sum);
    }

    /**
     * @return array{0: object, 1: float}
     */
    private function buildOrmUserFixture(): array
    {
        $ledgerEntries = new Collection([
            $this->makeLedgerEntry(
                id: 'entry-1',
                walletId: 'wallet-1',
                amount: 200.0,
                type: LedgerEntryType::credit->value,
                operation: LedgerOperation::manual->value,
            ),
            $this->makeLedgerEntry(
                id: 'entry-2',
                walletId: 'wallet-1',
                amount: 50.0,
                type: LedgerEntryType::debit->value,
                operation: LedgerOperation::transfer->value,
                transferId: 'transfer-99'
            ),
        ]);

        $wallet = new class() {
            public string $id = 'wallet-1';
            public string $user_id = 'user-1';
            public Collection $ledgerEntries;
        };
        $wallet->ledgerEntries = $ledgerEntries;

        $ormUser = new ORMUser();
        $ormUser->setAttribute('id', 'user-1');
        $ormUser->setAttribute('full_name', 'Jane Roe');
        $ormUser->setAttribute('kind', UserKind::common->value);
        $ormUser->setAttribute('document_type', DocumentType::cpf->value);
        $ormUser->setAttribute('document', '12345678900');
        $ormUser->setAttribute('email', 'jane@example.com');
        $ormUser->setAttribute('password', 'strong-password');
        $ormUser->wallet = $wallet;

        $expectedBalance = 150.0;

        return [$ormUser, $expectedBalance];
    }

    private function makeLedgerEntry(
        string $id,
        string $walletId,
        float $amount,
        string $type,
        string $operation,
        ?string $transferId = null
    ): object {
        $entry = new class() {
            public string $id;
            public string $wallet_id;
            public float $amount;
            public string $type;
            public string $operation;
            public ?string $transfer_id;
        };

        $entry->id = $id;
        $entry->wallet_id = $walletId;
        $entry->amount = $amount;
        $entry->type = $type;
        $entry->operation = $operation;
        $entry->transfer_id = $transferId;

        return $entry;
    }
}
