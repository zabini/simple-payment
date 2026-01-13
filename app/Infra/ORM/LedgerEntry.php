<?php

declare(strict_types=1);

namespace App\Infra\ORM;

class LedgerEntry extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'ledger_entries';

    /**
     * Cast attributes to native types.
     */
    protected array $casts = [
        'id' => 'string',
        'wallet_id' => 'string',
        'amount' => 'float',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'wallet_id',
        'amount',
        'type',
        'operation',
    ];
}
