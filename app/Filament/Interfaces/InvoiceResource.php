<?php

namespace App\Filament\Interfaces;

use App\Filament\Traits\InvoiceActions;
use App\Filament\Traits\InvoiceLikeFilters;
use App\Filament\Traits\InvoiceLikeFormFields;
use App\Filament\Traits\InvoiceLikeFillByProduct;
use App\Models\Product;
use App\Models\PurchaseInvoiceItem;
use Filament\Resources\Resource;
use Filament\Forms\Set;
use Filament\Forms\Get;

abstract class InvoiceResource extends Resource
{
    use InvoiceActions ,InvoiceLikeFormFields , InvoiceLikeFilters , InvoiceLikeFillByProduct;
}
