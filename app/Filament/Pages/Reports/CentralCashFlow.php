<?php

namespace App\Filament\Pages\Reports;

use App\Services\Reports\CentralCashFlowService;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class CentralCashFlow extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $routePath = 'central-cash-flow-report';

    protected static ?string $navigationGroup = 'إدارة الحسابات';

    protected static ?string $navigationLabel = 'تقرير المركز المالي';

    protected static ?string $title = 'تقرير المركز المالي';

    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فترة التقرير')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاريخ البداية')
                            ->default(now()->subMonth())
                            ->maxDate(now()),
                        DatePicker::make('endDate')
                            ->label('تاريخ النهاية')
                            ->default(now())
                            ->maxDate(now()),
                    ])
                    ->columns(2),
            ]);
    }


    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CentralCashFlowAssetsWidget::class,
            \App\Filament\Widgets\CentralCashFlowResponsibilitiesWidget::class,
        ];
    }
}
