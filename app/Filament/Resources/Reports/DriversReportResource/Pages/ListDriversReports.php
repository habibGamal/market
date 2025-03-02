<?php

namespace App\Filament\Resources\Reports\DriversReportResource\Pages;

use App\Filament\Resources\Reports\DriversReportResource;
use App\Filament\Widgets\DriversStatsOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListDriversReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = DriversReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DriversStatsOverview::class,
        ];
    }
}
