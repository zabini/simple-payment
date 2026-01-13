<?php

declare(strict_types=1);

namespace App\Infra\ORM;

use Hyperf\Database\Model\Relations\HasMany;

/**
 * @property string $name
 * @property string $kind
 * @property string $document_type
 * @property string $document
 * @property string $email
 * @property string $password
 */
class Wallet extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'wallets';

    /**
     * Cast attributes to native types.
     */
    protected array $casts = [
        'id' => 'string',
        'user_id' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'user_id',
    ];

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'wallet_id', 'id');
    }
}
