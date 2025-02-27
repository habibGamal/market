<?php

namespace App\Filament\Resources\AccountantIssueNoteResource\Pages;

use App\Filament\Resources\AccountantIssueNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountantIssueNote extends EditRecord
{
    protected static string $resource = AccountantIssueNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
