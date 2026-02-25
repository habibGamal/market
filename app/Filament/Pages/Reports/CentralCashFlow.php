<?php

namespace App\Filament\Pages\Reports;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class CentralCashFlow extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $routePath = 'central-cash-flow-report';

    protected static ?string $navigationGroup = 'إدارة الحسابات';

    protected static ?string $navigationLabel = 'تقرير المركز المالي';

    protected static ?string $title = 'تقرير المركز المالي';

    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('تصدير PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(fn () => route('reports.central-cash-flow'))
                ->openUrlInNewTab(),
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CentralCashFlowAssetsWidget::class,
            \App\Filament\Widgets\CentralCashFlowResponsibilitiesWidget::class,
        ];
    }
}
