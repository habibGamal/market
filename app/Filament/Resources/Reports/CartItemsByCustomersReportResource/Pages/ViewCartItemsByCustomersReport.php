<?php

namespace App\Filament\Resources\Reports\CartItemsByCustomersReportResource\Pages;

use App\Filament\Resources\Reports\CartItemsByCustomersReportResource;
use App\Filament\Resources\Reports\CartItemsByCustomersReportResource\RelationManagers\CartItemsRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCartItemsByCustomersReport extends ViewRecord
{
    protected static string $resource = CartItemsByCustomersReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }


    public function getRelationManagers(): array
    {
        return [
            CartItemsRelationManager::class,
        ];
    }
}
