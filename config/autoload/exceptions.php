<?php

declare(strict_types=1);

use App\Exception\Handler\AppExceptionHandler;
use App\Infra\Http\Exception\Handler as DomainExceptionHandler;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\Validation\ValidationExceptionHandler;

return [
    'handler' => [
        'http' => [
            DomainExceptionHandler::class,
            HttpExceptionHandler::class,
            ValidationExceptionHandler::class,
            AppExceptionHandler::class,
        ],
    ],
];
