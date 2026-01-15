<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\UserRepository;
use App\Core\Domain\Exceptions\NotFound;
use App\Core\Domain\User\User;
use App\Core\Domain\User\UserFactory;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string,User> */
    private array $byId = [];

    /** @var array<string,string> */
    private array $emailToId = [];

    /** @var array<string,string> */
    private array $documentToId = [];

    public function __construct(private UserFactory $factory)
    {
    }

    public function save(User $user): void
    {
        $this->byId[$user->getId()] = $user;
        $this->emailToId[$user->getEmail()] = $user->getId();
        $this->documentToId[$user->getDocument()] = $user->getId();
    }

    public function getOneById(string $id): User
    {
        if (! isset($this->byId[$id])) {
            throw NotFound::entityWithId('user', $id);
        }

        return $this->byId[$id];
    }

    public function getOneOrNullByEmail(string $email): ?User
    {
        if (! isset($this->emailToId[$email])) {
            return null;
        }

        return $this->byId[$this->emailToId[$email]];
    }

    public function getOneOrNullByDocument(string $document): ?User
    {
        if (! isset($this->documentToId[$document])) {
            return null;
        }

        return $this->byId[$this->documentToId[$document]];
    }
}
