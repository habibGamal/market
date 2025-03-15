<?php

namespace App\Filament\Resources\Reports\OrdersByCustomersReportResource\Pages;

use App\Filament\Resources\Reports\OrdersByCustomersReportResource;
use App\Filament\Widgets\OrdersByCustomersStatsOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListOrdersByCustomersReports extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = OrdersByCustomersReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OrdersByCustomersStatsOverview::class
        ];
    }
}
