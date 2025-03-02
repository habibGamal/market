<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ExpensesReportResource\Pages\ListExpensesReports;
use App\Services\Reports\ExpenseReportService;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpensesStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListExpensesReports::class;
    }

    protected function getStats(): array
    {
        if (empty($this->tableFilters['report_filter'])) {
            $startDate = now()->startOfMonth();
            $endDate = now();
        } else {
            $startDate = Carbon::parse($this->tableFilters['report_filter']['start_date']);
            $endDate = Carbon::parse($this->tableFilters['report_filter']['end_date']);
        }

        $expenseReportService = app(ExpenseReportService::class);

        $totalApproved = $expenseReportService->getTotalApprovedExpenses($startDate, $endDate);
        $totalNotApproved = $expenseReportService->getTotalNotApprovedExpenses($startDate, $endDate);
        $totalExpenses = $totalApproved + $totalNotApproved;

        $purchaseIssueNotes = $expenseReportService->getTotalPurchaseAccountantIssueNotes($startDate, $endDate);
        $driverReceiptNotes = $expenseReportService->getTotalDriverAccountantReceiptNotes($startDate, $endDate);
        $purchaseReturnReceiptNotes = $expenseReportService->getTotalPurchaseReturnAccountantReceiptNotes($startDate, $endDate);

        return [
            Stat::make('إجمالي المصروفات', number_format($totalExpenses, 2) . ' جنيه')
                ->description('إجمالي جميع المصروفات في الفترة المحددة')
                ->color('gray'),

            Stat::make('المصروفات المعتمدة', number_format($totalApproved, 2) . ' جنيه')
                ->description(sprintf('%.1f%% من إجمالي المصروفات', $totalExpenses > 0 ? ($totalApproved / $totalExpenses * 100) : 0))
                ->color('success'),

            Stat::make('المصروفات غير المعتمدة', number_format($totalNotApproved, 2) . ' جنيه')
                ->description(sprintf('%.1f%% من إجمالي المصروفات', $totalExpenses > 0 ? ($totalNotApproved / $totalExpenses * 100) : 0))
                ->color('danger'),

            Stat::make('إجمالي أذونات الصرف للمشتريات', number_format($purchaseIssueNotes, 2) . ' جنيه')
                ->description('إجمالي قيمة أذونات الصرف للمشتريات')
                ->color('warning'),

            Stat::make('إجمالي أذونات القبض من السائقين', number_format($driverReceiptNotes, 2) . ' جنيه')
                ->description('إجمالي قيمة أذونات القبض من السائقين')
                ->color('success'),

            Stat::make('إجمالي أذونات القبض من مرتجعات المشتريات', number_format($purchaseReturnReceiptNotes, 2) . ' جنيه')
                ->description('إجمالي قيمة أذونات القبض من مرتجعات المشتريات')
                ->color('info'),
        ];
    }
}
