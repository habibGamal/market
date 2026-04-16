<?php

namespace App\Filament\Resources\StockholderProfitExtractionResource\Pages;

use App\Filament\Resources\StockholderProfitExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockholderProfitExtraction extends ViewRecord
{
    protected static string $resource = StockholderProfitExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
