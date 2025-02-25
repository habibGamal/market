<?php

namespace App\Filament\Driver\Resources\ReturnItemResource\Pages;

use App\Filament\Driver\Resources\ReturnItemResource;
use App\Enums\ReturnOrderStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReturnItems extends ListRecords
{
    protected static string $resource = ReturnItemResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label('الكل'),
            'pending' => Tab::make()
                ->label('في انتظار الاستلام')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReturnOrderStatus::DRIVER_PICKUP))
                ->badge(fn () => static::getModel()::query()
                    ->where('driver_id', auth()->id())
                    ->where('status', ReturnOrderStatus::DRIVER_PICKUP)
                    ->count()
                ),
        ];
    }
}
