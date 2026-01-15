<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Integration\Http;

use App\Core\Domain\Contracts\Enum\TransferStatus;
use App\Core\Domain\Transfer;
use App\Core\Domain\User\User;
use HyperfTest\Integration\Http\HttpIntegrationTestCase;

/**
 * @internal
 * @covers \App\Infra\Http\Controller\TransferController
 */
class TransferHttpTest extends HttpIntegrationTestCase
{
    public function testCreatesPendingTransfer(): void
    {
        $payerId = $this->createUser([
            'email' => 'payer@example.com',
            'document' => '11111111111',
        ]);
        $payeeId = $this->createUser([
            'email' => 'payee@example.com',
            'document' => '22222222222',
        ]);

        $this->post("/user/{$payerId}/deposit", [
            'amount' => 200.0,
        ])->assertStatus(201);

        $response = $this->post('/transfer', [
            'payer' => $payerId,
            'payee' => $payeeId,
            'amount' => 75.0,
        ])->assertStatus(201);

        $payload = json_decode((string) $response->getBody()->getContents(), true);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('id', $payload);

        $transferId = $payload['id'];
        $transfer = $this->transferRepository->getOneById($transferId);
        $this->assertInstanceOf(Transfer::class, $transfer);
        $this->assertSame(TransferStatus::pending, $transfer->getStatus());
        $this->assertSame(75.0, $transfer->getAmount());
        $this->assertSame(
            $this->userRepository->getOneById($payerId)->getWallet()->getId(),
            $transfer->getPayerWallet()->getId()
        );
        $this->assertSame(
            $this->userRepository->getOneById($payeeId)->getWallet()->getId(),
            $transfer->getPayeeWallet()->getId()
        );
    }

    private function createUser(array $override = []): string
    {
        $payload = array_merge([
            'full_name' => 'John Doe',
            'kind' => 'common',
            'document_type' => 'cpf',
            'document' => '00000000000',
            'email' => 'john@example.com',
            'password' => 'secret',
        ], $override);

        $this->post('/user', $payload)->assertStatus(201);

        $created = $this->userRepository->getOneOrNullByEmail($payload['email']);
        $this->assertInstanceOf(User::class, $created);

        return $created->getId();
    }
}
