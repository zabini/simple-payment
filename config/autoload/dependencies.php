<?php

declare(strict_types=1);

use App\Core\Domain\Contracts\ExternalAuthorizer;
use App\Infra\Event\Publisher;
use App\Infra\Integration\Http\ExternalAuthorizer as ExternalAuthorizerHttp;
use App\Infra\Persistence\TransferRepository;
use App\Infra\Persistence\UserRepository;
use App\Infra\Persistence\WalletRepository;

return [
    App\Core\Domain\Contracts\UserRepository::class => UserRepository::class,
    App\Core\Domain\Contracts\WalletRepository::class => WalletRepository::class,
    App\Core\Domain\Contracts\TransferRepository::class => TransferRepository::class,
    ExternalAuthorizer::class => ExternalAuthorizerHttp::class,
    App\Core\Domain\Contracts\Event\Publisher::class => Publisher::class,
];
