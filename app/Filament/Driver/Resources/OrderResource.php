<?php

namespace App\Filament\Driver\Resources;

use App\Enums\DriverStatus;
use App\Enums\OrderStatus;
use App\Filament\Driver\Resources\OrderResource\Pages;
use App\Filament\Driver\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Driver\Resources\OrderResource\RelationManagers\ReturnItemsRelationManager;
use App\Filament\Driver\Resources\OrderResource\RelationManagers\CancelledItemsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                    'lg' => 4,
                ])
                    ->schema([
                        Stack::make([
                            Tables\Columns\TextColumn::make('id')
                                ->label('رقم الطلب')
                                ->size('lg')
                                ->weight('bold')
                                ->sortable()
                                ->searchable()
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('created_at')
                                ->label('التاريخ')
                                ->dateTime()
                                ->sortable()
                                ->color('gray')
                                ->formatSateUsingLabelPrefix(),
                        ])->space(1),
                        Stack::make([
                            Tables\Columns\TextColumn::make('customer.name')
                                ->label('العميل')
                                ->searchable()
                                ->weight('medium')
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make(name: 'customer.phone')
                                ->label('رقم الهاتف')
                                ->searchable()
                                ->color('gray')
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('customer.area.name')
                                ->label('المنطقة')
                                ->sortable()
                                ->searchable()
                                ->color('gray')
                                ->formatSateUsingLabelPrefix(),
                        ])->space(1),
                        Tables\Columns\TextColumn::make('driverTask.status')
                            ->label('الحالة')
                            ->badge()
                            ->sortable()
                            ->grow(false),
                        Tables\Columns\TextColumn::make('total')
                            ->label('المجموع')
                            ->money('egp')
                            ->sortable()
                            ->size('lg')
                            ->weight('bold')
                            ->color('success')
                            ->formatSateUsingLabelPrefix(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('driverTask.status')
                    ->query(
                        fn($query, $data) => !empty($data['values'])
                            ? $query->whereHas(
                                'driverTask',
                                fn($query) => $query->whereIn('status', $data['values'])
                              )
                            : $query
                    )
                    ->label('الحالة')
                    ->multiple()
                    ->options(DriverStatus::toSelectArray()),
                Tables\Filters\SelectFilter::make('area')
                    ->label('المنطقة')
                    ->multiple()
                    ->preload()
                    ->relationship('customer.area', 'name')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات الطلب')
                    ->schema([
                        TextEntry::make('id')
                            ->label('رقم الطلب')
                            ->formatStateUsing(fn(string $state): string => "#{$state}"),
                        TextEntry::make('total')
                            ->label('المجموع')
                            ->money('EGP')
                            ->color('green'),
                        TextEntry::make('status')
                            ->label('حالة الطلب')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime(),
                    ])->columns(3),

                Section::make('معلومات العميل')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('اسم العميل'),
                        TextEntry::make('customer.phone')
                            ->label('رقم الهاتف')
                            ->url(fn($record) => "tel:{$record->customer->phone}"),
                        TextEntry::make('customer.area.name')
                            ->label('المنطقة'),
                        TextEntry::make('customer.address')
                            ->label('العنوان')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            ReturnItemsRelationManager::class,
            CancelledItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('driverTask', function ($query) {
                $query->where('driver_id', auth()->id());
            });
    }
}
