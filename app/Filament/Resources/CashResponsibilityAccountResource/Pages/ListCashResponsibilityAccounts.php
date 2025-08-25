<?php

namespace App\Filament\Resources\CashResponsibilityAccountResource\Pages;

use App\Filament\Resources\CashResponsibilityAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashResponsibilityAccounts extends ListRecords
{
    protected static string $resource = CashResponsibilityAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
