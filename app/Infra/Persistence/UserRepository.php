<?php

declare(strict_types=1);

namespace App\Infra\Persistence;

use App\Core\Domain\Contracts\Enum\LedgerEntryType;
use App\Core\Domain\Contracts\Enum\LedgerOperation;
use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Contracts\UserRepository as UserRepositoryInterface;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\LedgerEntry;
use App\Core\Domain\User\User;
use App\Core\Domain\User\UserFactory;
use App\Core\Domain\Wallet;
use App\Infra\ORM\Transfer;
use App\Infra\ORM\User as ORMUser;
use App\Infra\ORM\Wallet as ORMWallet;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private UserFactory $factory)
    {
    }

    public function save(User $user): void
    {
        $ormUser = $this->newOrmUser([
            'id' => $user->getId(),
            'full_name' => $user->getFullName(),
            'kind' => $user->getKind()->value,
            'document_type' => $user->getDocumentType()->value,
            'document' => $user->getDocument(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ]);

        $ormUser->save();

        $ormWallet = $this->newOrmWallet([
            'id' => $user->getWallet()->getId(),
            'user_id' => $user->getWallet()->getUserId(),
        ]);

        $ormWallet->save();
    }

    public function getOneById(string $id): User
    {
        $ormUser = $this->userQuery()->find($id);
        if (! $ormUser instanceof ORMUser) {
            throw NotFound::entityWithId('user', $id);
        }

        return $this->rebuild($ormUser);
    }

    public function getOneOrNullByEmail(string $email): ?User
    {
        $ormUser = $this->userQuery()
            ->where('email', $email)
            ->first();

        if (! $ormUser instanceof ORMUser) {
            return null;
        }

        return $this->rebuild($ormUser);
    }

    public function getOneOrNullByDocument(string $document): ?User
    {
        $ormUser = $this->userQuery()
            ->where('document', $document)
            ->first();

        if (! $ormUser instanceof ORMUser) {
            return null;
        }

        return $this->rebuild($ormUser);
    }

    private function rebuild(ORMUser $ormUser): User
    {
        return $this->factory->create(
            id: $ormUser->id,
            fullName: $ormUser->full_name,
            kind: $ormUser->kind,
            documentType: $ormUser->document_type,
            document: $ormUser->document,
            email: $ormUser->email,
            password: $ormUser->password,
            wallet: Wallet::create(
                id: $ormUser->wallet->id,
                userId: $ormUser->wallet->user_id,
                committedBalance: $this->loadCommitedBalance($ormUser->wallet->id),
                ledgerEntries: $ormUser->wallet->ledgerEntries
                    ->map(fn ($ledgerEntry) => LedgerEntry::create(
                        walletId: $ledgerEntry->wallet_id,
                        amount: $ledgerEntry->amount,
                        type: LedgerEntryType::from($ledgerEntry->type),
                        operation: LedgerOperation::from($ledgerEntry->operation),
                        id: $ledgerEntry->id,
                        transferId: $ledgerEntry->transfer_id
                    ))
                ->all(),
            )
        );
    }

    protected function newOrmUser(array $attributes): ORMUser
    {
        return new ORMUser($attributes);
    }

    protected function newOrmWallet(array $attributes): ORMWallet
    {
        return new ORMWallet($attributes);
    }

    protected function userQuery()
    {
        return ORMUser::query()->with(['wallet.ledgerEntries']);
    }

    protected function transferQuery()
    {
        return Transfer::query();
    }

    private function loadCommitedBalance(string $walletId): float
    {
        return floatval($this->transferQuery()
            ->where('payer_wallet_id', $walletId)
            ->where('status', TransferStatus::pending->value)
            ->sum('amount'));
    }
}
