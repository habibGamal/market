<?php

namespace App\Filament\Resources\StockCountingResource\Pages;

use App\Filament\Resources\StockCountingResource;
use App\Filament\Traits\CreateAssignOfficer;
use App\Filament\Traits\InvoiceLikeCreateActions;
use App\Filament\Traits\InvoiceLikeCreateCloseHandler;
use Filament\Resources\Pages\CreateRecord;

class CreateStockCounting extends CreateRecord
{
    use CreateAssignOfficer, InvoiceLikeCreateCloseHandler, InvoiceLikeCreateActions;

    protected static string $resource = StockCountingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
