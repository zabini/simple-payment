<?php

declare(strict_types=1);

namespace App\Core\Application\User;

use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\User\User;
use App\Core\Domain\User\UserFactory;

class CreateHandler
{

    /**
     * @param UserRepository $repository
     * @param UserFactory $factory
     */
    public function __construct(
        private UserRepository $repository,
        private UserFactory $factory
    ) {}

    /**
     * @param CreateCommand $command
     * @return string
     */
    public function handle(CreateCommand $command): string
    {

        $user = $this->factory->create(
            fullName: $command->getFullName(),
            kind: $command->getKind(),
            documentType: $command->getDocumentType(),
            document: $command->getDocument(),
            email: $command->getEmail(),
            password: $command->getPassword()
        );

        $checkEmail = $this->repository->getOneOrNullByEmail($command->getEmail());
        if ($checkEmail instanceof User) {
            throw new \DomainException('Email already in use');
        }

        $checkDocument = $this->repository->getOneOrNullByDocument($command->getDocument());
        if ($checkDocument instanceof User) {
            throw new \DomainException('Document already in use');
        }

        $this->repository->save($user);

        return $user->getId();
    }
}
