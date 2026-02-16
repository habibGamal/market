<?php

namespace App\Observers;

use App\Models\VaultTransaction;
use Illuminate\Support\Facades\DB;

class VaultTransactionObserver
{
    public function creating(VaultTransaction $transaction): void
    {
        $transaction->user_id = auth()->id();
    }

    public function created(VaultTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Remove from source vault
            $fromVault = \App\Models\Vault::find($transaction->from_vault_id);
            $fromVault->balance -= $transaction->amount;
            $fromVault->save();

            // Add to destination vault
            $toVault = \App\Models\Vault::find($transaction->to_vault_id);
            $toVault->balance += $transaction->amount;
            $toVault->save();
        });
    }
}
