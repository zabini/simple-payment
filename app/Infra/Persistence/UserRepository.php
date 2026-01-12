<?php

namespace App\Infra\Persistence;

use App\Core\Domain\User\User;
use App\Core\Domain\Contracts\UserRepository as UserRepositoryInterface;
use App\Core\Domain\Exceptions\UserNotFound;
use App\Core\Domain\User\UserFactory;
use App\Core\Domain\Wallet;
use App\Infra\ORM\User as ORMUser;
use App\Infra\ORM\Wallet as ORMWallet;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private UserFactory $factory) {}

    /** @inheritDoc */
    public function save(User $user): void
    {
        $ormUser = new ORMUser([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'kind' => $user->getKind(),
            'document_type' => $user->getDocumentType(),
            'document' => $user->getDocument(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ]);

        $ormUser->save();

        $ormWallet = new ORMWallet([
            'id' => $user->getWallet()->getId(),
            'user_id' => $user->getWallet()->getUserId(),
            'balance' => $user->getWallet()->getBalance(),
        ]);

        $ormWallet->save();
    }

    /** @inheritDoc */
    public function getOneById(string $id): User
    {
        $ormUser = ORMUser::query()->find($id);
        if (!$ormUser instanceof ORMUser) {
            throw UserNotFound::withId($id);
        }

        return $this->rebuild($ormUser);
    }

    /** @inheritDoc */
    public function getOneOrNullByEmail(string $email): ?User
    {
        $ormUser = ORMUser::query()->where('email', $email)
            ->first();

        if (!$ormUser instanceof ORMUser) {
            return null;
        }

        return $this->rebuild($ormUser);
    }

    /** @inheritDoc */
    public function getOneOrNullByDocument(string $document): ?User
    {
        $ormUser = ORMUser::query()->where('document', $document)->first();

        if (!$ormUser instanceof ORMUser) {
            return null;
        }

        return $this->rebuild($ormUser);
    }

    /** @inheritDoc */
    private function rebuild(ORMUser $ormUser): User
    {
        return $this->factory->create(
            id: $ormUser->id,
            name: $ormUser->name,
            kind: $ormUser->kind,
            documentType: $ormUser->document_type,
            document: $ormUser->document,
            email: $ormUser->email,
            password: $ormUser->password,
            wallet: Wallet::create(
                id: $ormUser->wallet->id,
                userId: $ormUser->wallet->user_id,
                balance: $ormUser->wallet->balance,
            )
        );
    }
}
