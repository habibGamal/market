<?php

namespace App\Filament\Resources\CustomActivityLogResource\Pages;

use App\Filament\Resources\CustomActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomActivityLog extends CreateRecord
{
    protected static string $resource = CustomActivityLogResource::class;
}
