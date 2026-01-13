<?php

declare(strict_types=1);

namespace App\Core\Application\User;

class Create
{
    public function __construct(
        private string $fullName,
        private string $kind,
        private string $documentType,
        private string $document,
        private string $email,
        private string $password
    ) {
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getDocumentType(): string
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
}
