<?php

namespace App\Filament\Resources\CashSettlementResource\Pages;

use App\Filament\Resources\CashSettlementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashSettlements extends ListRecords
{
    protected static string $resource = CashSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
