<?php

namespace App\Filament\Resources\ShortTermLiabilityResource\Pages;

use App\Filament\Resources\ShortTermLiabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShortTermLiability extends EditRecord
{
    protected static string $resource = ShortTermLiabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
