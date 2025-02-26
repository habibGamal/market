<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use Filament\Resources\Pages\EditRecord;

class EditWaste extends EditRecord
{
    use InvoiceLikeEditActions, InvoiceLikeEditCloseHandler;

    protected static string $resource = WasteResource::class;
}
