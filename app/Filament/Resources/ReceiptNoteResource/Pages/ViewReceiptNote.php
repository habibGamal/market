<?php

namespace App\Filament\Resources\ReceiptNoteResource\Pages;

use App\Enums\ReceiptNoteType;
use App\Filament\Resources\ReceiptNoteResource;
use App\Filament\Resources\ReceiptNoteResource\RelationManagers\AccountantIssueNotesRelationManager;
use App\Filament\Resources\ReceiptNoteResource\RelationManagers\ItemsRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReceiptNote extends ViewRecord
{
    protected static string $resource = ReceiptNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function getRelationManagers(): array
    {
        return [
            ItemsRelationManager::class,
            ...($this->record->note_type === ReceiptNoteType::PURCHASES ? [AccountantIssueNotesRelationManager::class] : []),
        ];
    }

}
