<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\OrdersReportExporter;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\Reports\OrdersReportResource\Pages;
use App\Models\Order;
use App\Traits\ReportsFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class OrdersReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'إدارة المبيعات';
    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'الطلبية';

    protected static ?string $pluralModelLabel = 'تقارير الطلبات';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_report_order', Order::class);
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('view_report_order', Order::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount([
                    'items as brands_count' => function ($query) {
                        $query->join('products', 'order_items.product_id', '=', 'products.id')
                            ->distinct()
                            ->select(\DB::raw('COUNT(DISTINCT products.brand_id)'));
                    }
                ])
                ->addSelect([
                    'items_profit' => \App\Models\OrderItem::selectRaw('COALESCE(SUM(profit), 0)')
                        ->whereColumn('order_id', 'orders.id'),
                    'returns_total' => \App\Models\ReturnOrderItem::selectRaw('COALESCE(SUM(total), 0)')
                        ->whereColumn('order_id', 'orders.id'),
                    'returns_profit' => \App\Models\ReturnOrderItem::selectRaw('COALESCE(SUM(profit), 0)')
                        ->whereColumn('order_id', 'orders.id'),
                ])
                ->with(['returnItems', 'items']);
            })
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الطلبية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.area.name')
                    ->label('المنطقة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver.name')
                    ->label('مندوب التسليم')
                    ->sortable(),
                TextColumn::make('profit')
                    ->label('الربح')
                    ->money('EGP')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('items_profit', $direction);
                    })
                    ->visible(fn ()=> auth()->user()->can('view_profits_order', Order::class)),
                TextColumn::make('netProfit')
                    ->label('صافي الربح')
                    ->money('EGP')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(items_profit - returns_profit - discount) {$direction}");
                    })
                    ->visible(fn ()=> auth()->user()->can('view_profits_order', Order::class)),
                TextColumn::make('net_profit_percent')
                    ->label('نسبة صافي الربح')
                    ->getStateUsing(function (Order $record) {
                        $netTotal = $record->netTotal;
                        return $netTotal > 0 ? ($record->netProfit / $netTotal) * 100 : 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("
                            CASE
                                WHEN (total - returns_total - discount) > 0
                                THEN ((items_profit - returns_profit - discount) / (total - returns_total - discount)) * 100
                                ELSE 0
                            END {$direction}
                        ");
                    })
                    ->visible(fn ()=> auth()->user()->can('view_profits_order', Order::class)),
                TextColumn::make('total')
                    ->label('المجموع')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('netTotal')
                    ->label('الصافي')
                    ->money('EGP')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(total - returns_total - discount) {$direction}");
                    }),
                TextColumn::make('brands_count')
                    ->label('عدد الشركات')
                    ->sortable()
                    ->tooltip('عدد الشركات المختلفة في الطلب'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('customer.address')
                    ->label('العنوان')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->query(function (Builder $query, array $data): Builder {
                        logger()->info('Report Filter', $data);
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->where('created_at', '>=', \Carbon\Carbon::parse($date, 'Africa/Cairo')->utc()),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->where('created_at', '<=', \Carbon\Carbon::parse($date, 'Africa/Cairo')->utc()),
                            );
                    }),
                SelectFilter::make('area')
                    ->relationship('customer.area', 'name')
                    ->multiple()
                    ->label('المنطقة')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(\App\Enums\OrderStatus::class)
                    ->multiple()
                    ->label('الحالة')
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(OrdersReportExporter::class),
            ])
            ->actions([
                ViewAction::make(),
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
                            ->url(fn(Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                        TextEntry::make('total')
                            ->label('المجموع')
                            ->money('EGP')
                            ->tooltip('إجمالي قيمة الطلب قبل المرتجعات والخصومات'),
                        TextEntry::make('netTotal')
                            ->label('صافي المجموع')
                            ->money('EGP')
                            ->tooltip('إجمالي قيمة الطلب بعد خصم المرتجعات والخصومات'),
                        TextEntry::make('profit')
                            ->label('الربح')
                            ->money('EGP')
                            ->tooltip('إجمالي الربح قبل خصم المرتجعات والخصومات'),
                        TextEntry::make('netProfit')
                            ->label('صافي الربح')
                            ->money('EGP')
                            ->tooltip('صافي الربح بعد خصم المرتجعات والخصومات'),
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
                            ->label('اسم مندوب التسليم'),
                        TextEntry::make('driver.phone')
                            ->label('رقم هاتف مندوب التسليم'),
                        TextEntry::make('delivery_date')
                            ->label('تاريخ التوصيل')
                            ->dateTime(),
                    ])->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdersReports::route('/'),
            'view' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
        ];
    }
}
