<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Infra\ORM;

use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\Database\Model\Concerns\HasUuids;

abstract class Model extends BaseModel
{
    use HasUuids;

    protected string $primaryKey = 'id';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $casts = [
        'id' => 'string',
    ];
}
