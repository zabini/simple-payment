<?php

namespace App\Infra\Persistence;

use App\Core\Domain\User\User;
use App\Core\Domain\Contracts\UserRepository as UserRepositoryInterface;
use App\Core\Domain\User\UserFactory;
use App\Infra\ORM\User as ORMUser;

class UserRepository implements UserRepositoryInterface
{

    /**
     * @param User $user
     */
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
    }

    /**
     * @param string $id
     * @return User
     */
    public function getOneById(string $id): User
    {
        throw new \BadMethodCallException('UserRepository::getOneById not implemented');
    }

    public function getOneOrNullByEmail(string $email): ?User
    {
        // $ormUser = ORMUser::query()->where('email', $email);

        // if ($ormUser instanceof ORMUser) {
        //     // UserFactory::create(
        //     //     $ormUser->name,
        //     //     $ormUser->kind,
        //     //     $ormUser->document_type,
        //     //     $ormUser->document,
        //     //     $ormUser->mail,
        //     //     $ormUser->password
        //     // );
        // }

        return null;
    }

    public function getOneOrNullByDocument(string $document): ?User
    {
        // throw new \BadMethodCallException('UserRepository::getOneOrNullByDocument not implemented');
        return null;
    }
}
