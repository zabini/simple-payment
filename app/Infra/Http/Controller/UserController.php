<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Application\User\CreateCommand;
use App\Core\Application\User\CreateHandler;
use App\Infra\Http\Request\User\Create as UserCreateRequest;
use Laminas\Stdlib\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

class UserController extends AbstractController
{



    /**
     * @var CreateHandler
     */
    #[Inject]
    private CreateHandler $createHandler;

    // public function __construct(
    //     private CreateHandler $userCreateHandler
    // ) {}

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
        return [
            'id' =>  $id,
            'full_name' => 'John Doe',
            'kind' => 'common',
            'document_type' => 'cpf',
            'document' => '62412188084',
            'email' => 'john@otherexample.com',
        ];
    }
}
