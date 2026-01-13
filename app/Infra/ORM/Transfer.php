<?php

declare(strict_types=1);

namespace App\Infra\ORM;

class Transfer extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'transfers';

    /**
     * Cast attributes to native types.
     */
    protected array $casts = [
        'id' => 'string',
        'payer_id' => 'string',
        'payee_id' => 'string',
        'amount' => 'float',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'payer_id',
        'payee_id',
        'amount',
        'status',
        'failed_reason',
    ];
}
