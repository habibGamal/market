<?php

namespace App\Filament\Driver\Resources\OrderResource\Pages;

use App\Filament\Driver\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
