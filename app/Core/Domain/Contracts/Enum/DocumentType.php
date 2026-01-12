<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Enum;

enum DocumentType: string
{
    case cpf = 'cpf';
    case cnpj = 'cnpj';
}
