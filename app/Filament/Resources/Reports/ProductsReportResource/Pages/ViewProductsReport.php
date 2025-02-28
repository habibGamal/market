<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\Pages;

use App\Filament\Resources\Reports\ProductsReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductsReport extends ViewRecord
{
    protected static string $resource = ProductsReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
