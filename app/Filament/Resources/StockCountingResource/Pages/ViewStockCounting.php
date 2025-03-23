<?php

namespace App\Filament\Resources\StockCountingResource\Pages;

use App\Filament\Resources\StockCountingResource;
use App\Filament\Resources\StockCountingResource\RelationManagers\ItemsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewStockCounting extends ViewRecord
{
    protected static string $resource = StockCountingResource::class;

    public function getRelationManagers(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }
}
