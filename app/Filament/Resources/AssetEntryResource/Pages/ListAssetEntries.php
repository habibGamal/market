<?php

namespace App\Filament\Resources\AssetEntryResource\Pages;

use App\Filament\Resources\AssetEntryResource;
use App\Filament\Widgets\CapitalPartnersPieChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetEntries extends ListRecords
{
    protected static string $resource = AssetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CapitalPartnersPieChart::class,
        ];
    }
}
