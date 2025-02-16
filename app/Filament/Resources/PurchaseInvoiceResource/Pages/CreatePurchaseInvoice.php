<?php

namespace App\Filament\Resources\PurchaseInvoiceResource\Pages;

use App\Filament\Resources\PurchaseInvoiceResource;
use App\Filament\Traits\CreateAssignOfficer;
use App\Filament\Traits\InvoiceLikeCreateActions;
use App\Filament\Traits\InvoiceLikeCreateCloseHandler;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseInvoice extends CreateRecord
{
    use CreateAssignOfficer, InvoiceLikeCreateCloseHandler, InvoiceLikeCreateActions;
    protected static string $resource = PurchaseInvoiceResource::class;

}
