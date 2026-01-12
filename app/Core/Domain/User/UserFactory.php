<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Exceptions\InvalidUser;

final class UserFactory
{

    /**
     * @param string $name
     * @param string $kind
     * @param string $documentType
     * @param string $document
     * @param string $mail
     * @param string $password
     * @return User
     */
    public function create(
        string $name,
        string $kind,
        string $documentType,
        string $document,
        string $mail,
        string $password
    ): User {
        $typedDocument = DocumentType::tryFrom($documentType);

        if ($typedDocument === null) {
            throw InvalidUser::invalidDocumentType($documentType);
        }

        return match ($kind) {
            UserKind::common->value => Common::create($name, $typedDocument, $document, $mail, $password),
            UserKind::seller->value => Seller::create($name, $typedDocument, $document, $mail, $password),
            default => throw InvalidUser::invalidUserType($kind),
        };
    }
}
