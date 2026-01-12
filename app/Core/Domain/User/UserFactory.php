<?php

declare(strict_types=1);

namespace App\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Exceptions\InvalidUser;
use App\Core\Domain\Wallet;
use Ramsey\Uuid\Uuid;

final class UserFactory
{

    /**
     * @param string $name
     * @param string $kind
     * @param string $documentType
     * @param string $document
     * @param string $email
     * @param string $password
     * @param string|null $id
     * @return User
     */
    public function create(
        string $name,
        string $kind,
        string $documentType,
        string $document,
        string $email,
        string $password,
        ?string $id = null,
        ?Wallet $wallet = null,
    ): User {

        $typedDocument = DocumentType::tryFrom($documentType);
        if ($typedDocument === null) {
            throw InvalidUser::invalidDocumentType($documentType);
        }

        if ($id === null) {
            $id = Uuid::uuid4()->toString();
        }

        if ($wallet === null) {
            $wallet = Wallet::create($id);
        }

        return match ($kind) {
            UserKind::common->value => Common::make($id, $name, $typedDocument, $document, $email, $password, $wallet),
            UserKind::seller->value => Seller::make($id, $name, $typedDocument, $document, $email, $password, $wallet),
            default => throw InvalidUser::invalidUserType($kind),
        };
    }
}
