<?php

namespace App\Filament\Resources\ReturnPurchaseInvoiceResource\Pages;

use App\Filament\Resources\ReturnPurchaseInvoiceResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use Filament\Resources\Pages\EditRecord;

class EditReturnPurchaseInvoice extends EditRecord
{
    use InvoiceLikeEditActions, InvoiceLikeEditCloseHandler;

    protected static string $resource = ReturnPurchaseInvoiceResource::class;
}
