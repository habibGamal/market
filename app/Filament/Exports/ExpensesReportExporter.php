<?php

namespace App\Filament\Exports;

use App\Models\ExpenseType;
use App\Services\Reports\ExpenseReportService;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class ExpensesReportExporter extends Exporter
{
    protected static ?string $model = ExpenseType::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('نوع المصروف'),
            ExportColumn::make('expenses_count')
                ->label('عدد المصروفات'),
            ExportColumn::make('total_value')
                ->label('إجمالي المصروفات')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('approved_value')
                ->label('المصروفات المعتمدة')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('not_approved_value')
                ->label('المصروفات غير المعتمدة')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
        ];
    }

    protected function applyFiltersToQuery(Builder $query): Builder
    {
        return app(ExpenseReportService::class)->getFilteredQuery(
            $query,
            $this->getFilterState(),
        );
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير المصروفات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
