<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label('الكل'),
            'assignable_to_drivers' => Tab::make()
                ->label('يمكن تعيينها للسائقين')
                ->modifyQueryUsing(fn(Builder $query) => $query->assignableToDrivers()),
            'needs_issue_note' => Tab::make()
                ->label('تحتاج الي اذن صرف')
                ->modifyQueryUsing(fn(Builder $query) => $query->needsIssueNote()),
        ];
    }
}
