<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Core\Application\User\Create;
use App\Core\Application\User\CreateHandler;
use App\Core\Application\User\FetchById;
use App\Core\Application\User\FetchByIdHandler;
use App\Infra\Http\Request\User\Create as UserCreateRequest;
use Hyperf\Di\Annotation\Inject;
use Laminas\Stdlib\ResponseInterface;

class UserController extends AbstractController
{
    #[Inject]
    private CreateHandler $createHandler;

    #[Inject]
    private FetchByIdHandler $fetchByIdHandler;

    /**
     * @return ResponseInterface
     */
    public function create(UserCreateRequest $request)
    {
        $id = $this->createHandler->handle(
            new Create(
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

    public function fetchById(string $id)
    {
        $user = $this->fetchByIdHandler->handle(
            new FetchById($id)
        );

        return [
            'id' => $user->getId(),
            'full_name' => $user->getFullName(),
            'kind' => $user->getKind(),
            'document_type' => $user->getDocumentType(),
            'document' => $user->getDocument(),
            'email' => $user->getEmail(),
            'wallet' => [
                'balance' => $user->getWallet()->getBalance(),
            ],
        ];
    }
}
