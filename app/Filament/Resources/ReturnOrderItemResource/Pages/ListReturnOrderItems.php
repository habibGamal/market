<?php

namespace App\Filament\Resources\ReturnOrderItemResource\Pages;

use App\Filament\Resources\ReturnOrderItemResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\ReturnOrderStatus;

class ListReturnOrderItems extends ListRecords
{
    protected static string $resource = ReturnOrderItemResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label('الكل'),
            'pending' => Tab::make()
                ->label('في انتظار التعيين للسائق')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReturnOrderStatus::PENDING)),
        ];
    }
}
