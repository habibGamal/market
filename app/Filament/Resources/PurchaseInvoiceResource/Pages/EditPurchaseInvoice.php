<?php

namespace App\Filament\Resources\PurchaseInvoiceResource\Pages;

use App\Filament\Resources\PurchaseInvoiceResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use App\Filament\Traits\InvoiceLikeTrackChanges;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseInvoice extends EditRecord
{
    use InvoiceLikeEditActions , InvoiceLikeEditCloseHandler , InvoiceLikeTrackChanges;

    protected static string $resource = PurchaseInvoiceResource::class;

}
