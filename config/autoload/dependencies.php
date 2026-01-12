<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    \App\Core\Domain\Contracts\UserRepository::class => \App\Infra\Persistence\UserRepository::class,
    \App\Core\Domain\Contracts\WalletRepository::class => \App\Infra\Persistence\WalletRepository::class,
    \App\Core\Domain\Contracts\Event\Publisher::class => \App\Infra\Event\Publisher::class,
];
