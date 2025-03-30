<?php

namespace App\Filament\Resources\AssetEntryResource\Pages;

use App\Filament\Resources\AssetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetEntry extends CreateRecord
{
    protected static string $resource = AssetEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the officer_id to the currently authenticated user
        $data['officer_id'] = auth()->id();

        return $data;
    }
}
