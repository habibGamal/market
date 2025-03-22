<?php

namespace App\Filament\Resources\StockCountingResource\Pages;

use App\Filament\Resources\StockCountingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockCountings extends ListRecords
{
    protected static string $resource = StockCountingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
