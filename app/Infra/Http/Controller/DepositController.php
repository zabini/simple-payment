<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Core\Application\User\DepositCommand;
use App\Core\Application\User\DepositHandler;
use App\Infra\Http\Request\User\Deposit as UserDepositRequest;
use Hyperf\Di\Annotation\Inject;

class DepositController extends AbstractController
{
    #[Inject]
    private DepositHandler $depositHandler;

    public function deposit(string $id, UserDepositRequest $request)
    {
        $this->depositHandler->handle(
            new DepositCommand(
                $id,
                $request->input('amount'),
            )
        );

        return $this->response->raw('')->withStatus(201);
    }
}
