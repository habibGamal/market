<?php

namespace App\Models;

use App\Observers\VaultTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([VaultTransactionObserver::class])]
class VaultTransaction extends Model
{
    protected $fillable = [
        'from_vault_id',
        'to_vault_id',
        'amount',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function fromVault(): BelongsTo
    {
        return $this->belongsTo(Vault::class, 'from_vault_id');
    }

    public function toVault(): BelongsTo
    {
        return $this->belongsTo(Vault::class, 'to_vault_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
