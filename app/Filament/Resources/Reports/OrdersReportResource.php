<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\OrdersReportResource\Pages;
use App\Models\Order;
use App\Services\Reports\OrderReportService;
use App\Traits\ReportsFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class OrdersReportResource extends Resource
{
    use ReportsFilter;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'الطلبية';

    protected static ?string $pluralModelLabel = 'تقارير الطلبات';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->label('السائق')
                    ->sortable(),
                TextColumn::make('profit')
                    ->label('الربح')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('net_profit')
                    ->label('صافي الربح')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('المجموع')
                    ->money('EGP'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),


            ])
            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                            ->label('رقم الطلب'),
                        TextEntry::make('total')
                            ->label('المجموع')
                            ->money('EGP'),
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
                            ->label('اسم السائق'),
                        TextEntry::make('driver.phone')
                            ->label('رقم هاتف السائق'),
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
            'view' => Pages\ViewOrdersReport::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }
}
