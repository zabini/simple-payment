<?php

declare(strict_types=1);

namespace App\Core\Application\User;

class FetchByIdCommand
{

    /**
     * @param string $id
     */
    public function __construct(private string $id) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}
