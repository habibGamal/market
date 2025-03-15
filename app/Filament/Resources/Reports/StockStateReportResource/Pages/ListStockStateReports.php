<?php

namespace App\Filament\Resources\Reports\StockStateReportResource\Pages;

use App\Filament\Resources\Reports\StockStateReportResource;
use App\Filament\Widgets\StockStateStatsOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListStockStateReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = StockStateReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            StockStateStatsOverview::class,
        ];
    }
}
