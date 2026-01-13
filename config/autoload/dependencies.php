<?php

declare(strict_types=1);
use App\Infra\Event\Publisher;
use App\Infra\Persistence\UserRepository;
use App\Infra\Persistence\WalletRepository;

/*
 * This file is part of Hyperf.
 *
 * @see     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    App\Core\Domain\Contracts\UserRepository::class => UserRepository::class,
    App\Core\Domain\Contracts\WalletRepository::class => WalletRepository::class,
    App\Core\Domain\Contracts\Event\Publisher::class => Publisher::class,
];
