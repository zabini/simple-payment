<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts;

use App\Core\Domain\User\User;

interface UserRepository
{
    public function save(User $user): void;

    public function getOneById(string $id): User;

    public function getOneOrNullByEmail(string $email): ?User;

    public function getOneOrNullByDocument(string $document): ?User;
}
