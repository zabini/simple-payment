<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain\User;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\User\Common;
use App\Core\Domain\User\Seller;
use App\Core\Domain\Wallet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Core\Domain\User
 * @internal
 */
class UserTest extends TestCase
{
    public function testCommonUserCanTransfer(): void
    {
        $wallet = Wallet::create('user-1', 'wallet-1');
        $user = Common::make(
            'user-1',
            'John Doe',
            DocumentType::cpf,
            '12345678900',
            'john@example.com',
            'secret',
            $wallet
        );

        $this->assertTrue($user->canTransfer());
        $this->assertFalse($user->cantTransfer());
    }

    public function testSellerCannotTransfer(): void
    {
        $wallet = Wallet::create('seller-1', 'wallet-2');
        $user = Seller::make(
            'seller-1',
            'Acme Inc',
            DocumentType::cnpj,
            '00112233445566',
            'billing@acme.com',
            'secret',
            $wallet
        );

        $this->assertFalse($user->canTransfer());
        $this->assertTrue($user->cantTransfer());
    }

    public function testGettersReturnProvidedValues(): void
    {
        $wallet = Wallet::create('user-123', 'wallet-xyz');
        $user = Common::make(
            'user-123',
            'Jane Smith',
            DocumentType::cpf,
            '98765432100',
            'jane@example.com',
            'super-secret',
            $wallet
        );

        $this->assertSame('user-123', $user->getId());
        $this->assertSame('Jane Smith', $user->getFullName());
        $this->assertSame(DocumentType::cpf, $user->getDocumentType());
        $this->assertSame('98765432100', $user->getDocument());
        $this->assertSame('jane@example.com', $user->getEmail());
        $this->assertSame('super-secret', $user->getPassword());
        $this->assertSame($wallet, $user->getWallet());
        $this->assertSame('wallet-xyz', $user->getWallet()->getId());
    }
}
