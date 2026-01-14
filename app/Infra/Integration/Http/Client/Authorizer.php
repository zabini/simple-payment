<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Client;

use App\Infra\Integration\Http\Concerns\Client;
use App\Infra\Integration\Http\Response\AuthorizerResponse;
use Throwable;

use function Hyperf\Config\config;

class Authorizer extends Client
{

    public function requestAuthorization(string $payerId): bool
    {
        try {
            $decoded = $this->request('GET', 'api/v2/authorize', [
                'query' => [
                    'payer' => $payerId,
                ],
            ]);
            return (bool) $decoded?->data?->authorization;
        } catch (Throwable $th) {
            throw $th;
        }
    }

    protected function baseUri(): string
    {
        return (string) config('integration.authorizer.base_uri');
    }

    protected function bindAuth(?array $options): array
    {
        return $options ?? [];
    }

    protected function responseHandler(): string
    {
        return AuthorizerResponse::class;
    }
}
