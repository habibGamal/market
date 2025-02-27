<?php

namespace App\Filament\Resources\AccountantIssueNoteResource\Pages;

use App\Filament\Resources\AccountantIssueNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountantIssueNote extends ViewRecord
{
    protected static string $resource = AccountantIssueNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
