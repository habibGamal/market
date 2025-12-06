<?php

namespace App\Filament\Resources\DriverBalanceTrackerResource\Pages;

use App\Filament\Resources\DriverBalanceTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDriverBalanceTrackers extends ListRecords
{
    protected static string $resource = DriverBalanceTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
