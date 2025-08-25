<?php

namespace App\Filament\Resources\ShortTermLiabilityResource\Pages;

use App\Filament\Resources\ShortTermLiabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShortTermLiability extends CreateRecord
{
    protected static string $resource = ShortTermLiabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['officer_id'] = auth()->id();

        return $data;
    }
}
