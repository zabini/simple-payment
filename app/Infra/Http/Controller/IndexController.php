<?php

declare(strict_types=1);

namespace App\Infra\Http\Controller;

class IndexController extends AbstractController
{
    public function index()
    {
        return [
            'message' => 'It Works',
        ];
    }
}
