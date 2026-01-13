<?php

declare(strict_types=1);

namespace App\Core\Application\User;

use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\User\User;

class FetchByIdHandler
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    public function handle(FetchById $command): User
    {
        return $this->repository->getOneById($command->getId());
    }
}
