<?php

namespace App\Filament\Resources\ReturnPurchaseInvoiceResource\Pages;

use App\Filament\Resources\ReturnPurchaseInvoiceResource;
use App\Filament\Resources\ReturnPurchaseInvoiceResource\RelationManagers\ItemsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewReturnPurchaseInvoice extends ViewRecord
{
    protected static string $resource = ReturnPurchaseInvoiceResource::class;

    public function getRelationManagers(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }
}
