<?php

namespace App\Filament\Resources\StockItemResource\Pages;

use App\Filament\Resources\StockItemResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListStockItems extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockItemResource\Widgets\StockEvaluation::class,
        ];
    }
}
