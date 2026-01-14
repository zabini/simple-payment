<?php

declare(strict_types=1);

use App\Core\Domain\Event\Transfer\CompletedSubscriber as CompletedTransferSubscriber;
use App\Core\Domain\Event\Transfer\PendingCreatedSubscriber as PendingTransferCreatedSubscriber;
use Hyperf\Command\Listener\FailToHandleListener;
use Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler;

return [
    ErrorExceptionHandler::class,
    FailToHandleListener::class,
    CompletedTransferSubscriber::class,
    PendingTransferCreatedSubscriber::class,
];
