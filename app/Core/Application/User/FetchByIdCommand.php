<?php

declare(strict_types=1);

namespace App\Core\Application\User;

class FetchByIdCommand
{
    public function __construct(private string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
