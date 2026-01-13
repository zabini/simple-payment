<?php

declare(strict_types=1);

namespace App\Core\Application\User;

use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\User\User;

class FetchByIdHandler
{

    /**
     * @param UserRepository $repository
     */
    public function __construct(
        private UserRepository $repository
    ) {}

    /**
     * @param FetchByIdCommand $command
     * @return User
     */
    public function handle(FetchByIdCommand $command): User
    {
        return $this->repository->getOneById($command->getId());
    }
}
