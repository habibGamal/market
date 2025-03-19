<?php

namespace App\Filament\Resources\GovResource\Pages;

use App\Filament\Resources\GovResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGov extends EditRecord
{
    protected static string $resource = GovResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
