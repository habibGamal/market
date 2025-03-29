<?php

namespace App\Filament\Resources;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Driver;
use App\Models\DriverTask;
use App\Enums\OrderStatus;
use App\Enums\DriverStatus;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use App\Services\DriverServices;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'إدارة الطلبيات';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات الطلب')
                    ->schema([
                        TextEntry::make('id')
                            ->label('رقم الطلب'),
                        TextEntry::make('total')
                            ->label('المجموع')
                            ->money('EGP'),
                        TextEntry::make('netTotal')
                            ->label('الصافي')
                            ->money('EGP')
                            ->tooltip('إجمالي الطلب بعد خصم المرتجعات والخصومات'),
                        TextEntry::make('profit')
                            ->label('الربح')
                            ->money('EGP'),
                        TextEntry::make('netProfit')
                            ->label('صافي الربح')
                            ->money('EGP')
                            ->tooltip('صافي الربح بعد خصم المرتجعات والخصومات'),
                        TextEntry::make('status')
                            ->label('حالة الطلب')
                            ->badge(),
                        TextEntry::make('brands')
                            ->label('الشركات')
                            ->getStateUsing(function (Order $record) {
                                return $record->items()
                                    ->join('products', 'order_items.product_id', '=', 'products.id')
                                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                                    ->distinct('brands.id')
                                    ->pluck('brands.name')
                                    ->join('، ');
                            }),
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
                            ->label('رقم الهاتف'),
                        TextEntry::make('customer.email')
                            ->label('البريد الإلكتروني'),
                        TextEntry::make('customer.area.name')
                            ->label('المنطقة'),
                        TextEntry::make('customer.address')
                            ->label('العنوان')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('معلومات التوصيل')
                    ->schema([
                        TextEntry::make('driver.name')
                            ->label('اسم السائق'),
                        TextEntry::make('driver.phone')
                            ->label('رقم هاتف السائق'),
                        TextEntry::make('delivery_date')
                            ->label('تاريخ التوصيل')
                            ->dateTime(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('اسم العميل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.area.name')
                    ->label('المنطقة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('netTotal')
                    ->label('الصافي')
                    ->money('EGP')
                    ->tooltip('إجمالي الطلب بعد خصم المرتجعات والخصومات'),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('عدد الشركات')
                    ->getStateUsing(function (Order $record) {
                        return $record->items()
                            ->join('products', 'order_items.product_id', '=', 'products.id')
                            ->join('brands', 'products.brand_id', '=', 'brands.id')
                            ->distinct('brands.id')
                            ->count('brands.id');
                    })
                    ->sortable(false)
                    ->tooltip('عدد الشركات المختلفة في الطلب'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->multiple()
                    ->options(OrderStatus::class),
                SelectFilter::make('customer.area_id')
                    ->label('المنطقة')
                    ->multiple()
                    ->relationship('customer.area', 'name'),
                Filter::make('created_at')
                    ->form([
                        DateTimePicker::make('created_from')
                            ->label('من تاريخ'),
                        DateTimePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(OrderExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                    ->job('')
                    ->exporter(OrderExporter::class),
                    Tables\Actions\BulkAction::make('assignToDriver')
                        ->label('تعيين سائق')
                        ->icon('heroicon-o-truck')
                        ->form([
                            Forms\Components\Select::make('driver_id')
                                ->label('السائق')
                                ->options(Driver::driversOnly()->select(['id', 'name'])->get()->pluck('name', 'id'))
                                ->required()
                        ])
                        ->action(function ($records, array $data, $action) {
                            $filteredRecords = $records->filter(
                                fn($order) => $order->isAssinalbeToDriver
                            );

                            if ($filteredRecords->count() !== $records->count()) {
                                $action->failureNotification(
                                    Notification::make()
                                        ->title(
                                            'لا يمكن تعيين سائق للطلبات المحددة'
                                        )
                                        ->danger()
                                        ->send()
                                )->halt()->failure();
                            }
                            app(DriverServices::class)->assignOrdersToDriver($records, $data['driver_id']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('تعيين سائق للطلبات المحددة')
                        ->modalSubmitActionLabel('تعيين'),

                    Tables\Actions\BulkAction::make('createIssueNote')
                        ->label('إنشاء اذن صرف')
                        ->icon('heroicon-o-document-text')
                        ->action(function ($records, $action) {
                            $filteredRecords = $records->filter(
                                fn($order) => $order->isAbleToMakeIssueNote
                            );

                            if ($filteredRecords->count() !== $records->count()) {
                                $action->failureNotification(
                                    Notification::make()
                                        ->title(
                                            'لا يمكن إنشاء إذن صرف للطلبات المحددة'
                                        )
                                        ->danger()
                                        ->send()
                                )->halt()->failure();
                            }

                            $issueNote = \App\Models\IssueNote::create([
                                'officer_id' => auth()->id(),
                                'status' => \App\Enums\InvoiceStatus::DRAFT,
                                'note_type' => \App\Enums\IssueNoteType::ORDERS,
                                'total' => 0,
                            ]);

                            app(\App\Services\IssueNoteServices::class)
                                ->fromOrders($issueNote, $records);
                            $records->fresh()->each(fn($order) => notifyCustomerWithOrderStatus($order));
                            return redirect()->to(IssueNoteResource::getUrl('edit', ['record' => $issueNote]));
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('إنشاء اذن صرف للطلبات المحددة')
                        ->modalSubmitActionLabel('إنشاء'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\ReturnItemsRelationManager::class,
            RelationManagers\CancelledItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
