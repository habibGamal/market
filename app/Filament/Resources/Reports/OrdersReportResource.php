<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\OrdersReportResource\Pages;
use App\Filament\Widgets\OrderStatsOverview;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Reports\OrderReportService;
use App\Traits\ReportsFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersReportResource extends Resource
{
    use ReportsFilter;

    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير العميل';

    protected static ?string $pluralModelLabel = 'تقارير الطلبات والعملاء';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label('عدد الطلبات')
                    ->sortable(),
                TextColumn::make('total_sales')
                    ->label('قيمة المبيعات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total_profit')
                    ->label('الأرباح')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total_returns')
                    ->label('قيمة المرتجعات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total_cancelled')
                    ->label('قيمة الملغية')
                    ->money('EGP')
                    ->sortable()

            ])

            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        return app(OrderReportService::class)->getFilteredQuery($query, $data);
                    })
            ])
            ->actions([
                ViewAction::make(),
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
