<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Filament\Resources\WasteResource\RelationManagers\ItemsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewWaste extends ViewRecord
{
    protected static string $resource = WasteResource::class;


    public function getRelationManagers(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }
}
