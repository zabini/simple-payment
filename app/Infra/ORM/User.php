<?php

declare(strict_types=1);

namespace App\Infra\ORM;

/**
 * @property string $full_name
 * @property string $kind
 * @property string $document_type
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
     * Cast attributes to native types.
     */
    protected array $casts = [
        'id' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'full_name',
        'kind',
        'document_type',
        'document',
        'email',
        'password'
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'id');
    }
}
