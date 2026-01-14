<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http;

use App\Core\Domain\Contracts\Notifier as NotifierInterface;
use App\Infra\Integration\Http\Client\Notifier as NotifierClient;

use function Hyperf\Coroutine\go;

class Notifier implements NotifierInterface
{
    public function __construct(
        private NotifierClient $client
    ) {}

    public function notify(string $userId): void
    {
        go(fn() => $this->client->requestNotication($userId));
    }
}
