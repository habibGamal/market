<?php

namespace App\Filament\Resources\Reports\CartItemsByCustomersReportResource\Pages;

use App\Filament\Resources\Reports\CartItemsByCustomersReportResource;
use App\Filament\Widgets\CartTotalsByCustomersStatsOverview;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCartItemsByCustomersReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CartItemsByCustomersReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CartTotalsByCustomersStatsOverview::class,
        ];
    }
}
