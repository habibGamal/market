<?php

namespace App\Filament\Resources\IssueNoteResource\Pages;

use App\Enums\IssueNoteType;
use App\Filament\Resources\IssueNoteResource;
use App\Filament\Resources\IssueNoteResource\RelationManagers\AccountantReceiptNotesRelationManager;
use App\Filament\Resources\IssueNoteResource\RelationManagers\ItemsRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIssueNote extends ViewRecord
{
    protected static string $resource = IssueNoteResource::class;

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
            ...($this->record->note_type === IssueNoteType::RETURN_PURCHASES ? [AccountantReceiptNotesRelationManager::class] : []),
        ];
    }
}
