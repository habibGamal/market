<?php

namespace App\Filament\Resources\Reports\OrdersReportResource\Pages;

use App\Filament\Resources\Reports\OrdersReportResource;
use App\Filament\Widgets\OrderStatsOverview;
use App\Filament\Widgets\OrdersStatsChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListOrdersReports extends ListRecords
{
    use ExposesTableToWidgets,ReportsFilter;

    protected static string $resource = OrdersReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStatsOverview::class,
            OrdersStatsChart::class,
        ];
    }
}
