<?php

namespace App\Filament\Driver\Resources;

use App\Filament\Driver\Resources\ReturnItemResource\Pages;
use App\Models\ReturnOrderItem;
use App\Enums\ReturnOrderStatus;
use App\Services\DriverServices;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReturnItemResource extends Resource
{
    protected static ?string $model = ReturnOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $modelLabel = 'مرتجع';

    protected static ?string $pluralModelLabel = 'المرتجعات';

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('order.customer.name')
                    ->label('العميل')
                    ->collapsible()
            ])
            ->defaultGroup('order.customer.name')
            ->columns([
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                    'lg' => 4,
                ])
                    ->schema([
                        Stack::make([
                            Tables\Columns\TextColumn::make('order.id')
                                ->label('رقم الطلب')
                                ->size('lg')
                                ->weight('bold')
                                ->sortable()
                                ->searchable()
                                ->formatStateUsing(fn(string $state): string => "#{$state}")
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('created_at')
                                ->label('التاريخ')
                                ->dateTime()
                                ->sortable()
                                ->color('gray')
                                ->formatSateUsingLabelPrefix(),
                        ])->space(1),

                        Stack::make([
                            Tables\Columns\TextColumn::make('product.name')
                                ->label('المنتج')
                                ->weight('medium')
                                ->searchable()
                                ->formatSateUsingLabelPrefix(),
                            Stack::make([
                                Tables\Columns\TextColumn::make('packets_quantity')
                                    ->label('عدد العبوات')
                                    ->color('gray')
                                    ->formatSateUsingLabelPrefix(),
                                Tables\Columns\TextColumn::make('piece_quantity')
                                    ->label('عدد القطع')
                                    ->color('gray')
                                    ->formatSateUsingLabelPrefix(),
                            ])->space(1),
                        ])->space(1),

                        Stack::make([
                            Tables\Columns\TextColumn::make('order.customer.area.name')
                                ->label('المنطقة')
                                ->searchable()
                                ->sortable()
                                ->weight('medium')
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('order.customer.name')
                                ->label('العميل')
                                ->searchable()
                                ->color('gray')
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('order.customer.phone')
                                ->label('رقم الهاتف')
                                ->searchable()
                                ->color('gray')
                                ->formatSateUsingLabelPrefix(),
                        ])->space(1),


                        Stack::make([
                            Tables\Columns\TextColumn::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->sortable()
                                ->grow(false),
                            Tables\Columns\TextColumn::make('total')
                                ->label('الإجمالي')
                                ->money('EGP')
                                ->sortable()
                                ->size('lg')
                                ->weight('bold')
                                ->color('success')
                                ->formatSateUsingLabelPrefix()
                        ])->space(1),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area')
                    ->label('المنطقة')
                    ->relationship('order.customer.area', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_as_received')
                    ->label('تحديد كمستلم')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد استلام المرتجعات')
                    ->modalDescription('هل أنت متأكد من استلام المرتجعات المحددة؟')
                    ->modalSubmitActionLabel('تأكيد')
                    ->color('success')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, $action) {
                        $records->each(function (ReturnOrderItem $record) use ($action) {
                            if ($record->status !== ReturnOrderStatus::DRIVER_PICKUP) {
                                $action->failureNotification(
                                    Notification::make()
                                        ->title('خطأ')
                                        ->body('لا يمكن استلام المرتجعات التي ليست في حالة استلام السائق.')
                                        ->danger()
                                        ->send()
                                )->halt()->failure();
                            }
                        });
                        app(DriverServices::class)->markReturnItemsAsReceivedFromCustomer($records);
                        notifyCustomerWithReturnOrderStatus($records->first()->order, ReturnOrderStatus::RECEIVED_FROM_CUSTOMER->value);
                    })
            ])
            ->checkIfRecordIsSelectableUsing(
                fn($record): bool => $record->status === ReturnOrderStatus::DRIVER_PICKUP,
            )
        ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnItems::route('/'),
            'view' => Pages\ViewReturnItem::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('driver_id', auth()->id())
            ->with(['order.customer', 'product']);
    }
}
