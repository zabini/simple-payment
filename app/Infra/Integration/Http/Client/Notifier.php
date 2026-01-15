<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Client;

use App\Infra\Exception\GatewayTimeoutException;
use App\Infra\Integration\Http\Concerns\Client;
use App\Infra\Integration\Http\Response\NotifierResponse;
use GuzzleHttp\Exception\ClientException;

use function Hyperf\Config\config;

class Notifier extends Client
{
    public function requestNotication(string $userId): void
    {
        [$maxAttempts, $baseDelayMs] = $this->retryConfig();

        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                $this->performRequest($userId);
                return;
            } catch (GatewayTimeoutException $exception) {
                if (! $this->shouldRetry($attempt, $maxAttempts)) {
                    throw $exception;
                }
                $this->backoff($attempt, $baseDelayMs);
            } catch (ClientException $exception) {
                throw $exception;
            }
        }
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

    /**
     * @return array{int, int} [maxAttempts, baseDelayMs]
     */
    private function retryConfig(): array
    {
        $maxAttempts = max(1, (int) config('integration.notifier.max_attempts', 3));
        $baseDelayMs = max(1, (int) config('integration.notifier.backoff_base_ms', 250));

        return [$maxAttempts, $baseDelayMs];
    }

    private function performRequest(string $userId): void
    {
        $this->request('POST', 'api/v1/notify', [
            'json' => [
                'payer' => $userId,
            ],
        ]);
    }

    private function shouldRetry(int $attempt, int $maxAttempts): bool
    {
        return $attempt < $maxAttempts;
    }

    private function backoff(int $attempt, int $baseDelayMs): void
    {
        $delayMs = $baseDelayMs * (2 ** ($attempt - 1));
        usleep($delayMs * 1000);
    }
}
