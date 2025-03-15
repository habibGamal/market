<?php

namespace App\Filament\Resources\Reports\OrdersByAreasReportResource\Pages;

use App\Filament\Resources\Reports\OrdersByAreasReportResource;
use App\Filament\Widgets\OrdersByAreasStatsOverview;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListOrdersByAreasReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrdersByAreasReportResource::class;


    protected function getHeaderWidgets(): array
    {
        return [
            OrdersByAreasStatsOverview::class
        ];
    }
}
