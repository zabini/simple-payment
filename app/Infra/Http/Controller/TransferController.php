<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Core\Application\User\Transfer;
use App\Core\Application\User\TransferHandler;
use App\Infra\Http\Request\Transfer\Create as TransferCreateRequest;
use Hyperf\Di\Annotation\Inject;

class TransferController extends AbstractController
{
    #[Inject]
    private TransferHandler $transferHandler;

    public function create(TransferCreateRequest $request)
    {
        $id = $this->transferHandler->handle(
            new Transfer(
                $request->input('payer'),
                $request->input('payee'),
                $request->input('amount')
            )
        );

        return $this->response->json([
            'id' => $id,
        ])->withStatus(201);
    }
}
