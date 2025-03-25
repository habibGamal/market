<?php

namespace App\Filament\Resources\Reports\CartItemsByProductsReportResource\Pages;

use App\Filament\Resources\Reports\CartItemsByProductsReportResource;
use App\Filament\Resources\Reports\CartItemsByProductsReportResource\RelationManagers\CartItemsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewCartItemsByProductsReport extends ViewRecord
{
    protected static string $resource = CartItemsByProductsReportResource::class;

    public function getRelationManagers(): array
    {
        return [
            CartItemsRelationManager::class,
        ];
    }
}
