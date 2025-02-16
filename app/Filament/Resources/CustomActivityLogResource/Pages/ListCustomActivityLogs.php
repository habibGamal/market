<?php

namespace App\Filament\Resources\CustomActivityLogResource\Pages;

use App\Filament\Resources\CustomActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomActivityLogs extends ListRecords
{
    protected static string $resource = CustomActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
