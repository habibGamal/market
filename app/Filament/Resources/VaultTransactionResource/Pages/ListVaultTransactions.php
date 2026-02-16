<?php

namespace App\Filament\Resources\VaultTransactionResource\Pages;

use App\Filament\Resources\VaultTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVaultTransactions extends ListRecords
{
    protected static string $resource = VaultTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
