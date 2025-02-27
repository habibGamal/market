<?php

namespace App\Filament\Resources\AccountantReceiptNoteResource\Pages;

use App\Filament\Resources\AccountantReceiptNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountantReceiptNote extends EditRecord
{
    protected static string $resource = AccountantReceiptNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
