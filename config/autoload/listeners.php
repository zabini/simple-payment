<?php

declare(strict_types=1);

use App\Core\Domain\Event\PendingTransferCreatedSubscriber;
use Hyperf\Command\Listener\FailToHandleListener;
use Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler;

return [
    ErrorExceptionHandler::class,
    FailToHandleListener::class,
    PendingTransferCreatedSubscriber::class,
];
