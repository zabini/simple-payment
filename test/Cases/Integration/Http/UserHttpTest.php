<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Integration\Http;

use App\Core\Domain\User\User;
use HyperfTest\Integration\Http\HttpIntegrationTestCase;

/**
 * @internal
 * @covers \App\Infra\Http\Controller\DepositController
 * @covers \App\Infra\Http\Controller\UserController
 */
class UserHttpTest extends HttpIntegrationTestCase
{
    public function testCreatesUserAndFetchesIt(): void
    {
        $userId = $this->createUser([
            'email' => 'john.doe@example.com',
            'document' => '12345678900',
        ]);

        $created = $this->userRepository->getOneById($userId);
        $this->assertInstanceOf(User::class, $created);
        $this->assertSame('john.doe@example.com', $created->getEmail());

        $this->get("/user/{$userId}")
            ->assertOk()
            ->assertJson([
                'id' => $userId,
                'full_name' => 'John Doe',
                'kind' => 'common',
                'document_type' => 'cpf',
                'document' => '12345678900',
                'email' => 'john.doe@example.com',
                'wallet' => [
                    'balance' => 0.0,
                ],
            ]);
    }

    public function testDepositsIntoUserWallet(): void
    {
        $userId = $this->createUser([
            'email' => 'alice@example.com',
            'document' => '98765432100',
        ]);

        $this->post("/user/{$userId}/deposit", [
            'amount' => 150.0,
        ])->assertStatus(201);

        $user = $this->userRepository->getOneById($userId);
        $this->assertEquals(150.0, $user->getWallet()->getBalance());

        $response = $this->get("/user/{$userId}")
            ->assertOk();

        $payload = json_decode((string) $response->getBody()->getContents(), true);
        $this->assertIsArray($payload);
        $this->assertEquals(150.0, $payload['wallet']['balance']);
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
