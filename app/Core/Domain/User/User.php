<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Wallet;

abstract class User
{
    protected function __construct(
        private string $id,
        private string $fullName,
        private UserKind $kind,
        private DocumentType $documentType,
        private string $document,
        private string $email,
        private string $password,
        private Wallet $wallet
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getKind(): UserKind
    {
        return $this->kind;
    }

    public function getDocumentType(): DocumentType
    {
        return $this->documentType;
    }

    public function getDocument(): string
    {
        return $this->document;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function cantTransfer(): bool
    {
        return ! $this->canTransfer();
    }

    abstract public function canTransfer(): bool;
}
