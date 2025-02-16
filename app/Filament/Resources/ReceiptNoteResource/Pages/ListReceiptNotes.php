<?php

namespace App\Filament\Resources\ReceiptNoteResource\Pages;

use App\Filament\Resources\ReceiptNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceiptNotes extends ListRecords
{
    protected static string $resource = ReceiptNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
