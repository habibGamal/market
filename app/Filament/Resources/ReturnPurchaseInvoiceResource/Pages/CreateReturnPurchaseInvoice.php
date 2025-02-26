<?php

namespace App\Filament\Resources\ReturnPurchaseInvoiceResource\Pages;

use App\Filament\Resources\ReturnPurchaseInvoiceResource;
use App\Filament\Traits\CreateAssignOfficer;
use App\Filament\Traits\InvoiceLikeCreateActions;
use App\Filament\Traits\InvoiceLikeCreateCloseHandler;
use Filament\Resources\Pages\CreateRecord;

class CreateReturnPurchaseInvoice extends CreateRecord
{
    use CreateAssignOfficer, InvoiceLikeCreateCloseHandler, InvoiceLikeCreateActions;

    protected static string $resource = ReturnPurchaseInvoiceResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record'=>$this->record]);
    }
}
