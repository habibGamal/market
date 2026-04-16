<?php

namespace App\Filament\Resources\StockholderResource\Pages;

use App\Filament\Resources\StockholderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockholder extends ViewRecord
{
    protected static string $resource = StockholderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
