<?php

namespace App\Filament\Resources\ProductsShortageReportResource\Pages;

use App\Filament\Resources\ProductsShortageReportResource;
use Filament\Resources\Pages\ListRecords;

class ListProductsShortageReport extends ListRecords
{
    protected static string $resource = ProductsShortageReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
