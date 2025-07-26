<?php

namespace App\Filament\Resources\CashSettlementAccountResource\Pages;

use App\Filament\Resources\CashSettlementAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashSettlementAccounts extends ListRecords
{
    protected static string $resource = CashSettlementAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
