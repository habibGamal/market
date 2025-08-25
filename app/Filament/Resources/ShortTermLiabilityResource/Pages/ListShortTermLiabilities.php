<?php

namespace App\Filament\Resources\ShortTermLiabilityResource\Pages;

use App\Filament\Resources\ShortTermLiabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShortTermLiabilities extends ListRecords
{
    protected static string $resource = ShortTermLiabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
