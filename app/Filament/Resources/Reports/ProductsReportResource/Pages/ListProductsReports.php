<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\Pages;

use App\Filament\Resources\Reports\ProductsReportResource;
use App\Filament\Widgets\ProductsStatsOverview;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListProductsReports extends ListRecords
{

    use ExposesTableToWidgets;

    protected static string $resource = ProductsReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }


    protected function getHeaderWidgets(): array
    {
        return [
            ProductsStatsOverview::class,
        ];
    }
}
