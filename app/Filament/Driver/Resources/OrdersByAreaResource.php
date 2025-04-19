<?php

namespace App\Filament\Driver\Resources;

use App\Enums\DriverStatus;
use App\Filament\Driver\Resources\OrdersByAreaResource\Pages;
use App\Models\Area;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersByAreaResource extends Resource
{
    protected static ?string $model = Area::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'تقرير خط السير';
    protected static ?string $modelLabel = 'منطقة';
    protected static ?string $pluralModelLabel = 'تقرير خط السير';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('المنطقة')
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label('عدد الطلبات المتبقية')
                    ->counts([
                        'orders' => fn(Builder $query) => $query
                            ->whereHas(
                                'driverTask',
                                fn(Builder $q) =>
                                $q->where('driver_id', auth()->id())
                                    ->where('status', '!=', DriverStatus::DONE)
                            ),
                    ])
                    ->sortable()
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas(
                'orders',
                fn(Builder $query) =>
                $query->whereHas(
                    'driverTask',
                    fn(Builder $q) =>
                    $q->where('driver_id', auth()->id())
                        ->where('status', '!=', DriverStatus::DONE)
                )
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdersByArea::route('/'),
        ];
    }
}
