<?php

namespace App\Filament\Resources\RevenueResource\Pages;

use App\Filament\Resources\RevenueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRevenue extends CreateRecord
{
    protected static string $resource = RevenueResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['officer_id'] = auth()->id();

        return $data;
    }
}
