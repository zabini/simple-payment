<?php

declare(strict_types=1);

use Hyperf\AsyncQueue\Driver\RedisDriver;

return [
    'default' => [
        'driver' => RedisDriver::class,
        'redis' => [
            'pool' => 'default',
        ],
        'channel' => 'async-queue',
        'timeout' => 2,
        'retry_seconds' => 5,   // tempo entre retries
        'max_attempts' => 3,    // número máximo de tentativas
        'handle_timeout' => 10,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
];
