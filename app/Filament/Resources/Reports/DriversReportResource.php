<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\DriversReportResource\Pages;
use App\Models\Driver;
use App\Services\Reports\DriverReportService;
use App\Traits\ReportsFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DriversReportResource extends Resource
{
    use ReportsFilter;

    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير مندوب التسليم';

    protected static ?string $pluralModelLabel = 'تقارير مندوبين التسليم';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم مندوب التسليم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pending_orders_count')
                    ->label('عدد الطلبات قيد التسليم')
                    ->sortable(),
                TextColumn::make('out_for_delivery_total')
                    ->label('قيمة الطلبات قيد التسليم')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total_returns')
                    ->label('قيمة المرتجعات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('account.balance')
                    ->label('الرصيد')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        return app(DriverReportService::class)->getFilteredQuery($query, $data);
                    })
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(\App\Filament\Exports\DriversReportExporter::class),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriversReports::route('/'),
            'view' => Pages\ViewDriversReport::route('/{record}'),
        ];
    }
}
