<?php

namespace App\Filament\Resources\NotificationManagerResource\Pages;

use App\Filament\Resources\NotificationManagerResource;
use App\Jobs\ProcessNotificationJob;
use Filament\Resources\Pages\CreateRecord;

class CreateNotificationManager extends CreateRecord
{
    protected static string $resource = NotificationManagerResource::class;

    protected function afterCreate(): void
    {
        $notification = $this->record;

        if (!$notification->schedule_at) {
            ProcessNotificationJob::dispatch($notification);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
