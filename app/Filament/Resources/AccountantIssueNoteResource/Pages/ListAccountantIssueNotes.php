<?php

namespace App\Filament\Resources\AccountantIssueNoteResource\Pages;

use App\Filament\Resources\AccountantIssueNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountantIssueNotes extends ListRecords
{
    protected static string $resource = AccountantIssueNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
