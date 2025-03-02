<?php

namespace App\Filament\Resources\Reports\CategoriesReportResource\Pages;

use App\Filament\Resources\Reports\CategoriesReportResource;
use App\Filament\Widgets\CategoriesStatsOverview;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCategoriesReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CategoriesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CategoriesStatsOverview::class,
        ];
    }
}
