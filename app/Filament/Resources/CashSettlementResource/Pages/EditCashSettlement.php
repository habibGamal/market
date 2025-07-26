<?php

namespace App\Filament\Resources\CashSettlementResource\Pages;

use App\Filament\Resources\CashSettlementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashSettlement extends EditRecord
{
    protected static string $resource = CashSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
