<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\OrdersByAreasReportExporter;
use App\Filament\Resources\Reports\OrdersByAreasReportResource\Pages;
use App\Filament\Widgets\OrdersByAreasStatsOverview;
use App\Models\Area;
use App\Services\Reports\OrdersByAreasReportService;
use App\Traits\ReportsFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersByAreasReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'إدارة المبيعات';
    protected static ?int $navigationSort = 12;

    protected static ?string $modelLabel = 'تقرير المنطقة';

    protected static ?string $pluralModelLabel = 'تقارير الطلبات حسب المناطق';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_report_area', Area::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
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
                    ->label('الأرباح')
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn ()=> auth()->user()->can('view_profits_area', Area::class)),
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
                        return app(OrdersByAreasReportService::class)->getFilteredQuery($query, $data);
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(OrdersByAreasReportExporter::class),
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
            'index' => Pages\ListOrdersByAreasReports::route('/'),
            'view' => Pages\ViewOrdersByAreasReport::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrdersByAreasStatsOverview::class
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
        ];
    }
}
