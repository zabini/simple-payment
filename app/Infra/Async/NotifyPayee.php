<?php

declare(strict_types=1);

namespace App\Infra\Async;

use App\Core\Application\Transfer\NotifyPayee as TransferNotifyPayee;
use App\Core\Application\Transfer\NotifyPayeeHandler;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;

use function Hyperf\Config\config;

class NotifyPayee extends Job
{
    public function __construct(private string $transferId)
    {
        $this->setMaxAttempts(max(1, (int) config('integration.notifier.max_attempts', 5)));
    }

    public function handle()
    {
        $notifyPayeeHandler = ApplicationContext::getContainer()
            ->get(NotifyPayeeHandler::class);

        $notifyPayeeHandler->handle(
            new TransferNotifyPayee($this->transferId)
        );
    }
}
