<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Filament\Traits\CreateAssignOfficer;
use App\Filament\Traits\InvoiceLikeCreateActions;
use App\Filament\Traits\InvoiceLikeCreateCloseHandler;
use Filament\Resources\Pages\CreateRecord;

class CreateWaste extends CreateRecord
{
    use CreateAssignOfficer, InvoiceLikeCreateCloseHandler, InvoiceLikeCreateActions;

    protected static string $resource = WasteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
