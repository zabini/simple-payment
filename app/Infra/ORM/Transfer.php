<?php

declare(strict_types=1);

namespace App\Infra\ORM;

use Hyperf\Database\Model\Relations\BelongsTo;

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
        'payer_wallet_id' => 'string',
        'payee_wallet_id' => 'string',
        'amount' => 'float',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'payer_wallet_id',
        'payee_wallet_id',
        'amount',
        'status',
        'failed_reason',
    ];

    public function payerWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payer_wallet_id', 'id');
    }

    public function payeeWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payee_wallet_id', 'id');
    }
}
