<?php

namespace App\Services;

use App\Models\Vault;

class VaultService
{
    public function getVault(): Vault
    {
        return Vault::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'الخزينة النقدية',
                'balance' => 0,
            ]
        );
    }

    public function add(float $amount): Vault
    {
        $vault = $this->getVault();
        $vault->balance += $amount;
        $vault->save();

        return $vault;
    }

    public function remove(float $amount): Vault
    {
        $vault = $this->getVault();
        $vault->balance -= $amount;
        $vault->save();

        return $vault;
    }
}
