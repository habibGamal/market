<?php

namespace App\Filament\Traits;

trait CreateAssignOfficer
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['officer_id'] = auth()->id();

        return $data;
    }
}
