<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http;

use App\Core\Domain\Contracts\ExternalAuthorizer as AuthorizerInterface;
use App\Core\Domain\Exceptions\InvalidOperation;
use App\Infra\Integration\Http\Client\Authorizer;

class ExternalAuthorizer implements AuthorizerInterface
{
    public function __construct(
        private Authorizer $client
    ) {
    }

    public function authorize(string $userId): void
    {
        if (! $this->client->requestAuthorization($userId)) {
            throw InvalidOperation::fromExternalReason('Authorization failed');
        }
    }
}
