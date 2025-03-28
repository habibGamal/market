<?php

namespace App\Filament\Resources\AccountantIssueNoteResource\Pages;

use App\Filament\Resources\AccountantIssueNoteResource;
use App\Models\ReceiptNote;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountantIssueNote extends CreateRecord
{
    protected static string $resource = AccountantIssueNoteResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'for_model_type' => ReceiptNote::class
        ]);
    }
}
