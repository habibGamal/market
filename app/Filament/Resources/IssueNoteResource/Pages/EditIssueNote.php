<?php

namespace App\Filament\Resources\IssueNoteResource\Pages;

use App\Filament\Resources\IssueNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIssueNote extends EditRecord
{
    protected static string $resource = IssueNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
