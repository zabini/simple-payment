<?php

declare(strict_types=1);

use App\Core\Domain\Contracts\Notifier;
use App\Core\Domain\Contracts\TransferAuthorizer;
use App\Infra\Event\Publisher;
use App\Infra\Integration\Http\Notifier as NotifierHttp;
use App\Infra\Integration\Http\TransferAuthorizer as TransferAuthorizerHttp;
use App\Infra\Persistence\TransferRepository;
use App\Infra\Persistence\UserRepository;
use App\Infra\Persistence\WalletRepository;

return [
    App\Core\Domain\Contracts\UserRepository::class => UserRepository::class,
    App\Core\Domain\Contracts\WalletRepository::class => WalletRepository::class,
    App\Core\Domain\Contracts\TransferRepository::class => TransferRepository::class,
    TransferAuthorizer::class => TransferAuthorizerHttp::class,
    Notifier::class => NotifierHttp::class,
    App\Core\Domain\Contracts\Event\Publisher::class => Publisher::class,
];
