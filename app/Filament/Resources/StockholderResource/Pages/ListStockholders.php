<?php

namespace App\Filament\Resources\StockholderResource\Pages;

use App\Filament\Resources\StockholderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockholders extends ListRecords
{
    protected static string $resource = StockholderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
