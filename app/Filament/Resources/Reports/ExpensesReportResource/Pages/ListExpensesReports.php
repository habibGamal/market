<?php

namespace App\Filament\Resources\Reports\ExpensesReportResource\Pages;

use App\Filament\Resources\Reports\ExpensesReportResource;
use App\Filament\Widgets\ExpensesStatsOverview;
use App\Filament\Widgets\ExpensesChart;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListExpensesReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ExpensesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExpensesStatsOverview::class,
            ExpensesChart::class,
        ];
    }
}
