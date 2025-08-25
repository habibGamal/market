<?php

namespace App\Filament\Resources\CashResponsibilityAccountResource\Pages;

use App\Filament\Resources\CashResponsibilityAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashResponsibilityAccount extends EditRecord
{
    protected static string $resource = CashResponsibilityAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
