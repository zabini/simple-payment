<?php

declare(strict_types=1);

namespace HyperfTest\Cases\User;

use App\Core\Domain\Contracts\Enum\UserKind;
use Hyperf\Testing\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
class UserTest extends TestCase
{

    private $faker;

    protected function setUp(): void
    {

        $this->faker = \Faker\Factory::create();
    }

    public function testShouldCreateACommonUser()
    {
        $payload = $this->userPayload();

        $response = $this->post('/user', $payload);

        $this->assertTrue(Uuid::isValid($response->json('id')));
    }

    public function testShouldCreateTwoUsersWithDistinctIds()
    {
        $first = $this->post('/user', $this->userPayload([
            'document' => '62412188084',
        ]));
        $second = $this->post('/user', $this->userPayload([
            'document' => '28461103032',
        ]));

        $firstId = $first->json('id');
        $secondId = $second->json('id');

        $this->assertTrue(Uuid::isValid($firstId));
        $this->assertTrue(Uuid::isValid($secondId));
        $this->assertNotSame($firstId, $secondId);
    }

    /**
     * @param array $overrides
     * @return array
     */
    private function userPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'John Doe',
            'kind' => UserKind::common->value,
            'document_type' => 'cpf',
            'document' => $this->faker->cpf(),
            'email' => $this->faker->email(),
            'password' => 'strong-password',
        ], $overrides);
    }
}
