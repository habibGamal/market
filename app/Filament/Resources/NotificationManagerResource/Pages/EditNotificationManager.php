<?php

namespace App\Filament\Resources\NotificationManagerResource\Pages;

use App\Filament\Resources\NotificationManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationManager extends EditRecord
{
    protected static string $resource = NotificationManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->isProcessing() || $this->record->isCompleted()) {
            unset($data['title'], $data['body'], $data['filters'], $data['data']);
        }

        return $data;
    }
}
