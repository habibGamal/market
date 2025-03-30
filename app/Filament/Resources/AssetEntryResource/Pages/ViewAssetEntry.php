<?php

namespace App\Filament\Resources\AssetEntryResource\Pages;

use App\Filament\Resources\AssetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetEntry extends ViewRecord
{
    protected static string $resource = AssetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
