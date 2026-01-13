<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Exceptions\InvalidUser;
use App\Core\Domain\User\Common;
use App\Core\Domain\User\Seller;
use App\Core\Domain\User\UserFactory;
use App\Core\Domain\Wallet;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserFactoryTest extends TestCase
{
    public function testCreatesCommonUserWithDefaults(): void
    {
        $factory = new UserFactory();

        $user = $factory->create(
            fullName: 'John Doe',
            kind: UserKind::common->value,
            documentType: DocumentType::cpf->value,
            document: '12345678900',
            email: 'john@example.com',
            password: 'secret',
        );

        $this->assertInstanceOf(Common::class, $user);
        $this->assertTrue($user->canTransfer());
        $this->assertTrue(Uuid::isValid($user->getId()));
        $this->assertSame(UserKind::common, $user->getKind());
        $this->assertSame(DocumentType::cpf, $user->getDocumentType());
        $this->assertSame($user->getId(), $user->getWallet()->getUserId());
        $this->assertSame(0.0, $user->getWallet()->getBalance());
    }

    public function testCreatesSellerUser(): void
    {
        $factory = new UserFactory();

        $user = $factory->create(
            fullName: 'Acme Inc',
            kind: UserKind::seller->value,
            documentType: DocumentType::cnpj->value,
            document: '00112233445566',
            email: 'billing@acme.com',
            password: 'secret',
        );

        $this->assertInstanceOf(Seller::class, $user);
        $this->assertFalse($user->canTransfer());
        $this->assertSame(UserKind::seller, $user->getKind());
        $this->assertSame(DocumentType::cnpj, $user->getDocumentType());
    }

    public function testUsesProvidedIdAndWallet(): void
    {
        $factory = new UserFactory();
        $wallet = Wallet::create(
            userId: 'provided-user-id',
            id: 'wallet-123'
        );

        $user = $factory->create(
            fullName: 'Jane Roe',
            kind: UserKind::common->value,
            documentType: DocumentType::cpf->value,
            document: '99988877766',
            email: 'jane@example.com',
            password: 'secret',
            id: 'provided-user-id',
            wallet: $wallet,
        );

        $this->assertSame('provided-user-id', $user->getId());
        $this->assertSame($wallet, $user->getWallet());
        $this->assertSame('wallet-123', $user->getWallet()->getId());
        $this->assertSame(0.0, $user->getWallet()->getBalance());
    }

    public function testThrowsForInvalidDocumentType(): void
    {
        $factory = new UserFactory();

        $this->expectException(InvalidUser::class);
        $this->expectExceptionMessage('Provided document type (invalid) is invalid');

        $factory->create(
            fullName: 'Invalid Document',
            kind: UserKind::common->value,
            documentType: 'invalid',
            document: '123',
            email: 'invalid@example.com',
            password: 'secret',
        );
    }

    public function testThrowsForInvalidUserType(): void
    {
        $factory = new UserFactory();

        $this->expectException(InvalidUser::class);
        $this->expectExceptionMessage('Provided user type (unknown) is invalid');

        $factory->create(
            fullName: 'Invalid User',
            kind: 'unknown',
            documentType: DocumentType::cpf->value,
            document: '123',
            email: 'invalid@example.com',
            password: 'secret',
        );
    }
}
