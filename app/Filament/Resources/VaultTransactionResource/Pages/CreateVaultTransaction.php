<?php

namespace App\Filament\Resources\VaultTransactionResource\Pages;

use App\Filament\Resources\VaultTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVaultTransaction extends CreateRecord
{
    protected static string $resource = VaultTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
