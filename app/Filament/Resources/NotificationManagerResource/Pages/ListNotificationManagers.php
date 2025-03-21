<?php

namespace App\Filament\Resources\NotificationManagerResource\Pages;

use App\Filament\Resources\NotificationManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationManagers extends ListRecords
{
    protected static string $resource = NotificationManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء إشعار جديد'),
        ];
    }
}
