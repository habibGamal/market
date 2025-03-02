<?php

namespace App\Filament\Resources\Reports\BrandsReportResource\Pages;

use App\Filament\Resources\Reports\BrandsReportResource;
use App\Filament\Widgets\BrandsStatsOverview;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListBrandsReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = BrandsReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BrandsStatsOverview::class,
        ];
    }
}
