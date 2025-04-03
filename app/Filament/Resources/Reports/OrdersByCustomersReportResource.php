<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\OrdersByCustomersReportExporter;
use App\Filament\Resources\Reports\OrdersByCustomersReportResource\Pages;
use App\Filament\Widgets\OrdersByCustomersStatsOverview;
use App\Models\Customer;
use App\Services\Reports\OrdersByCustomersReportService;
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

class OrdersByCustomersReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير العميل';

    protected static ?string $pluralModelLabel = 'تقارير الطلبات حسب العملاء';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_report_orders_customer', Customer::class);
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('view_report_orders_customer', Customer::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area.name')
                    ->label('المنطقة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label('عدد الطلبات')
                    ->sortable(),
                TextColumn::make('total_sales')
                    ->label('قيمة المبيعات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total_profit')
                    ->label('صافي الأرباح')
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn() => auth()->user()->can('view_profits_customer', Customer::class)),
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
                        return app(OrdersByCustomersReportService::class)->getFilteredQuery($query, $data);
                    }),
                SelectFilter::make('area')
                    ->relationship('area', 'name')
                    ->multiple()
                    ->label('المنطقة')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(OrdersByCustomersReportExporter::class),
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
            'index' => Pages\ListOrdersByCustomersReports::route('/'),
            'view' => Pages\ViewOrdersByCustomersReport::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrdersByCustomersStatsOverview::class
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}
