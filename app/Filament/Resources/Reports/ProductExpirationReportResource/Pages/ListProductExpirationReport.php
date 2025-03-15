<?php

namespace App\Filament\Resources\Reports\ProductExpirationReportResource\Pages;

use App\Filament\Resources\Reports\ProductExpirationReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListProductExpirationReport extends ListRecords
{
    protected static string $resource = ProductExpirationReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
