<?php

declare(strict_types=1);

namespace App\Infra\ORM;

use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $name
 * @property UserKind $kind
 * @property DocumentType $document_type
 * @property string $document
 * @property string $email
 * @property string $password
 */
class User extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'name',
        'kind',
        'document_type',
        'document',
        'email',
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'kind' => UserKind::class,
        'document_type' => DocumentType::class,
    ];
}
