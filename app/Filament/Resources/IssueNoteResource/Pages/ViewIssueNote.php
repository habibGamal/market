<?php

namespace App\Filament\Resources\IssueNoteResource\Pages;

use App\Filament\Resources\IssueNoteResource;
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
}
