<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

use App\Core\Domain\User\User;

interface UserRepository
{

    /**
     * @param User $user
     */
    public function save(User $user): void;

    /**
     * @param string $id
     * @return User
     */
    public function getOneById(string $id): User;

    /**
     * @param string $email
     * @return User|null
     */
    public function getOneOrNullByEmail(string $email): ?User;

    /**
     * @param string $id
     * @return User|null
     */
    public function getOneOrNullByDocument(string $document): ?User;
}
