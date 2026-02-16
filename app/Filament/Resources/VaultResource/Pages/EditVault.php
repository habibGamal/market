<?php

namespace App\Filament\Resources\VaultResource\Pages;

use App\Filament\Resources\VaultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVault extends EditRecord
{
    protected static string $resource = VaultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
