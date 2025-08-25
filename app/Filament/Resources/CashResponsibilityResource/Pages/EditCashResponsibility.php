<?php

namespace App\Filament\Resources\CashResponsibilityResource\Pages;

use App\Filament\Resources\CashResponsibilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashResponsibility extends EditRecord
{
    protected static string $resource = CashResponsibilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
