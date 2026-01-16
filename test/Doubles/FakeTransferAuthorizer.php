<?php

declare(strict_types=1);

namespace HyperfTest\Doubles;

use App\Core\Domain\Contracts\TransferAuthorizer;
use App\Core\Domain\Exceptions\InvalidOperation;

final class FakeTransferAuthorizer implements TransferAuthorizer
{
    /** @var string[] */
    private array $authorizedUsers = [];

    private bool $allow = true;

    private ?string $failureReason = null;

    public function authorize(string $userId): void
    {
        if (! $this->allow) {
            throw InvalidOperation::fromExternalReason($this->failureReason ?? 'Authorization failed');
        }

        $this->authorizedUsers[] = $userId;
    }

    public function allow(): void
    {
        $this->allow = true;
        $this->failureReason = null;
    }

    public function denyWith(string $reason): void
    {
        $this->allow = false;
        $this->failureReason = $reason;
    }

    /**
     * @return string[]
     */
    public function authorized(): array
    {
        return $this->authorizedUsers;
    }
}
