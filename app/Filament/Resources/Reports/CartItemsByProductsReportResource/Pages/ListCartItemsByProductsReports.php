<?php

namespace App\Filament\Resources\Reports\CartItemsByProductsReportResource\Pages;

use App\Filament\Resources\Reports\CartItemsByProductsReportResource;
use App\Filament\Widgets\CartTotalsByProductsStatsOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCartItemsByProductsReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CartItemsByProductsReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CartTotalsByProductsStatsOverview::class,
        ];
    }
}
