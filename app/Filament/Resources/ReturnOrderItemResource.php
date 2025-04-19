<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ReturnOrderItemExporter;
use App\Filament\Resources\ReturnOrderItemResource\Pages;
use App\Models\Driver;
use App\Models\ReturnOrderItem;
use App\Models\User;
use App\Enums\ReturnOrderStatus;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ReceiptNote;
use App\Services\DriverServices;

class ReturnOrderItemResource extends Resource
{
    protected static ?string $model = ReturnOrderItem::class;

    protected static ?string $navigationGroup = 'إدارة المبيعات';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $modelLabel = 'مرتجع';
    protected static ?string $pluralModelLabel = 'المرتجعات';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات المرتجع')
                    ->schema([
                        TextEntry::make('order.id')
                            ->label('رقم الطلب'),
                        TextEntry::make('product.name')
                            ->label('المنتج'),
                        TextEntry::make('packets_quantity')
                            ->label('عدد العبوات الكلي'),
                        TextEntry::make('packet_price')
                            ->label('سعر العبوة')
                            ->money('egp'),
                        TextEntry::make('piece_quantity')
                            ->label('عدد القطع الكلي'),
                        TextEntry::make('piece_price')
                            ->label('سعر القطعة')
                            ->money('egp'),
                        TextEntry::make('total')
                            ->label('المجموع')
                            ->money('egp'),
                        TextEntry::make('return_reason')
                            ->label('سبب الإرجاع'),
                        TextEntry::make('notes')
                            ->label('ملاحظات'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                        TextEntry::make('driver.name')
                            ->label('مندوب التسليم'),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime(),
                    ])->columns(3),
                Section::make('معلومات العميل')
                    ->schema([
                        TextEntry::make('order.customer.name')
                            ->label('اسم العميل'),
                        TextEntry::make('order.customer.phone')
                            ->label('رقم الهاتف'),
                        TextEntry::make('order.customer.address')
                            ->label('العنوان'),
                        TextEntry::make('order.customer.gov.name')
                            ->label('المحافظة'),
                        TextEntry::make('order.customer.city.name')
                            ->label('المدينة'),
                        TextEntry::make('order.customer.area.name')
                            ->label('المنطقة'),

                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isNeedsReceiptNoteTab = request()->query('activeTab') === 'needsReceiptNote';
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('رقم الطلب')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات الكلي')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->money('egp')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع الكلي')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->money('egp')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->money('egp')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.customer.phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.customer.area.name')
                    ->label('المنطقة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('مندوب التسليم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ReturnOrderStatus::class),
                Tables\Filters\SelectFilter::make('driver')
                    ->label('مندوب التسليم')
                    ->relationship('driver', 'name'),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ReturnOrderItemExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ReturnOrderItemExporter::class),
                    Tables\Actions\BulkAction::make('assignToDriver')
                        ->label('تعيين مندوب تسليم')
                        ->icon('heroicon-o-truck')
                        ->requiresConfirmation()
                        ->modalHeading('تعيين مندوب تسليم للمرتجعات المحددة')
                        ->modalSubmitActionLabel('تعيين')
                        ->form([
                            \Filament\Forms\Components\Select::make('driver_id')
                                ->label('مندوب التسليم')
                                ->options(Driver::driversOnly()->select(['id', 'name'])->get()->pluck('name', 'id'))
                                ->required()
                        ])
                        ->action(function ($records, array $data) {
                            app(DriverServices::class)->assignReturnOrdersToDriver($records, $data['driver_id']);
                            notifyCustomerWithReturnOrderStatus($records->first()->order, ReturnOrderStatus::DRIVER_PICKUP->value);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف')
                        ->modalHeading('حذف المرتجعات المحددة')
                        ->modalSubmitActionLabel('حذف')
                        ->successNotificationTitle('تم حذف المرتجعات بنجاح'),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn($record): bool => $record->status !== ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnOrderItems::route('/'),
            'view' => Pages\ViewReturnOrderItem::route('/{record}'),
        ];
    }
}
