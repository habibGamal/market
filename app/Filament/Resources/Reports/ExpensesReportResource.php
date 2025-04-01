<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\ExpensesReportExporter;
use App\Filament\Resources\Reports\ExpensesReportResource\Pages;
use App\Filament\Widgets\ExpensesChart;
use App\Filament\Widgets\ExpensesStatsOverview;
use App\Models\ExpenseType;
use App\Services\Reports\ExpenseReportService;
use App\Traits\ReportsFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpensesReportResource extends Resource
{
    use ReportsFilter;


    protected static ?string $model = ExpenseType::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير المصروفات';

    protected static ?string $pluralModelLabel = 'تقارير المصروفات';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نوع المصروف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('expenses_count')
                    ->label('عدد المصروفات')
                    ->sortable(),
                TextColumn::make('total_value')
                    ->label('إجمالي المصروفات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('approved_value')
                    ->label('المصروفات المعتمدة')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('not_approved_value')
                    ->label('المصروفات غير المعتمدة')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        return app(ExpenseReportService::class)->getFilteredQuery($query, $data);
                    })
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ExpensesReportExporter::class),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->label('تصدير المحدد')
                    ->exporter(ExpensesReportExporter::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpensesReports::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ExpensesStatsOverview::class,
            ExpensesChart::class,
        ];
    }
}
