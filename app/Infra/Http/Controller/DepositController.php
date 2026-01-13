<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Core\Application\User\DepositCommand;
use App\Core\Application\User\DepositHandler;
use App\Infra\Http\Request\User\Deposit as UserDepositRequest;
use Hyperf\Di\Annotation\Inject;
use Laminas\Stdlib\ResponseInterface;

class DepositController extends AbstractController
{

    /**
     * @var DepositHandler
     */
    #[Inject]
    private DepositHandler $depositHandler;

    /**
     * @param string $id
     * @param UserDepositRequest $request
     */
    public function deposit(string $id, UserDepositRequest $request)
    {

        $this->depositHandler->handle(
            new DepositCommand(
                $id,
                $request->input('amount'),
            )
        );

        return $this->response->raw("")->withStatus(201);
    }
}
