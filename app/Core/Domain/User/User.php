<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use Ramsey\Uuid\Uuid;

abstract class User
{

    /**
     * @param string $id
     * @param string $name
     * @param UserKind $kind
     * @param DocumentType $documentType
     * @param string $document
     * @param string $email
     * @param string $password
     */
    protected function __construct(
        private string $id,
        private string $name,
        private UserKind $kind,
        private DocumentType $documentType,
        private string $document,
        private string $email,
        private string $password
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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

    /**
     * @param string $name
     * @param DocumentType $documentType
     * @param string $document
     * @param string $email
     * @param string $password
     * @return self
     */
    public static function create(
        string $name,
        DocumentType $documentType,
        string $document,
        string $email,
        string $password
    ): self {
        return new static(
            Uuid::uuid4()->toString(),
            $name,
            static::providesType(),
            $documentType,
            $document,
            $email,
            $password
        );
    }

    /**
     * @return boolean
     */
    abstract public function canTransfer(): bool;

    /**
     * @return UserKind
     */
    abstract public static function providesType(): UserKind;
}
