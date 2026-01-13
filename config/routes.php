<?php

declare(strict_types=1);

use App\Infra\Http\Controller\DepositController;
use App\Infra\Http\Controller\IndexController;
use App\Infra\Http\Controller\TransferController;
use App\Infra\Http\Controller\UserController;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', [IndexController::class, 'index']);

Router::post('/user', [UserController::class, 'create']);

Router::addGroup('/user/{id:[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}}', function () {
    Router::get('', [UserController::class, 'fetchById']);
    Router::post('/deposit', [DepositController::class, 'deposit']);
});

Router::post('/transfer', [TransferController::class, 'create']);
