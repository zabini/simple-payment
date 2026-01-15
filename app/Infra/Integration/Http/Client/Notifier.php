<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Client;

use App\Infra\Integration\Http\Concerns\Client;
use App\Infra\Integration\Http\Response\NotifierResponse;

use function Hyperf\Config\config;

class Notifier extends Client
{
    public function requestNotication(string $userId): void
    {
        $this->request('POST', 'api/v1/notify', [
            'json' => [
                'payer' => $userId,
            ],
        ]);
    }

    protected function baseUri(): string
    {
        return (string) config('integration.notifier.base_uri');
    }

    protected function bindAuth(?array $options): array
    {
        return $options ?? [];
    }

    protected function responseHandler(): string
    {
        return NotifierResponse::class;
    }
}
