<?php

namespace App\Filament\Resources\AccountantReceiptNoteResource\Pages;

use App\Filament\Resources\AccountantReceiptNoteResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAccountantReceiptNote extends CreateRecord
{
    protected static string $resource = AccountantReceiptNoteResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        try {
            $record = static::getModel()::create($data);
            return $record;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('خطأ في العملية')
                ->body($e->getMessage())
                ->send();
            $this->halt();
        }
    }
}
