<?php

declare(strict_types=1);

namespace App\Infra\ORM;

use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\DbConnection\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    use HasUuids;

    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $casts = [
        'id' => 'string',
    ];
}
