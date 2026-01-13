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

    public function testShouldCreateACommonUser()
    {
        $payload = $this->userPayload();

        $response = $this->post('/user', $payload);
        $this->assertTrue(Uuid::isValid($response->json('id')));
    }

    /**
     * @param array $overrides
     * @return array
     */
    private function userPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => "Jhon Doe",
            'kind' => UserKind::common->value,
            'document_type' => 'cpf',
            'document' => "99999999993",
            'email' => "xyz@example.com",
            'password' => 'strong-password',
        ], $overrides);
    }
}
