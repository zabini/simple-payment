<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

use App\Infra\Integration\Http\Client\Notifier as ClientsNotifier;

class IndexController extends AbstractController
{
    public function index()
    {
        var_dump((new ClientsNotifier())
            ->notify('sajkdajl'));

        return [
            'message' => 'It Works',
        ];
    }
}
