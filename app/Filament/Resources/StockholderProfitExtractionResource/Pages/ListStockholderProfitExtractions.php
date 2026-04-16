<?php

namespace App\Filament\Resources\StockholderProfitExtractionResource\Pages;

use App\Filament\Resources\StockholderProfitExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockholderProfitExtractions extends ListRecords
{
    protected static string $resource = StockholderProfitExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
