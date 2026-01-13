<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Core\Application\User\CreateCommand;
use App\Core\Application\User\CreateHandler;
use App\Core\Application\User\FetchByIdHandler;
use App\Core\Application\User\FetchByIdCommand;
use App\Infra\Http\Request\User\Create as UserCreateRequest;
use Hyperf\Di\Annotation\Inject;
use Laminas\Stdlib\ResponseInterface;

class UserController extends AbstractController
{

    /**
     * @var CreateHandler
     */
    #[Inject]
    private CreateHandler $createHandler;

    /**
     * @var FetchByIdHandler
     */
    #[Inject]
    private FetchByIdHandler $fetchByIdHandler;

    /**
     * @param UserCreateRequest $request
     * @return ResponseInterface
     */
    public function create(UserCreateRequest $request)
    {

        $id = $this->createHandler->handle(
            new CreateCommand(
                $request->input('full_name'),
                $request->input('kind'),
                $request->input('document_type'),
                $request->input('document'),
                $request->input('email'),
                $request->input('password'),
            )
        );

        return $this->response->json([
            'id' => $id,
        ])->withStatus(201);
    }

    /**
     * @param string $id
     */
    public function fetchById(string $id)
    {

        $user = $this->fetchByIdHandler->handle(
            new FetchByIdCommand($id)
        );

        return [
            'id' =>  $user->getId(),
            'full_name' => $user->getFullName(),
            'kind' => $user->getKind(),
            'document_type' => $user->getDocumentType(),
            'document' => $user->getDocument(),
            'email' => $user->getEmail(),
            'wallet' => [
                'balance' => $user->getWallet()->getBalance(),
            ]
        ];
    }
}
