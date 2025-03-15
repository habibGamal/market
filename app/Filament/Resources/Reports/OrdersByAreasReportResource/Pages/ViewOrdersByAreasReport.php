<?php

namespace App\Filament\Resources\Reports\OrdersByAreasReportResource\Pages;

use App\Filament\Resources\Reports\OrdersByAreasReportResource;
use App\Filament\Widgets\AreaOrdersChart;
use App\Filament\Widgets\AreaReturnsChart;
use App\Filament\Widgets\AreaCancelledOrdersChart;
use App\Services\Reports\OrdersByAreasReportService;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewOrdersByAreasReport extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = OrdersByAreasReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filter')
                ->label('تحديد الفترة')
                ->form(static::filtersForm())
                ->action(function (array $data): void {
                    if ($data['period'] !== static::PERIOD_CUSTOM) {
                        $range = static::getRange($data['period']);
                        $this->dispatch('updateChart', start: $range['start_date'], end: $range['end_date']);
                    } else {
                        $this->dispatch('updateChart', start: $data['start_date'], end: $data['end_date']);
                    }
                })
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $this->record = app(OrdersByAreasReportService::class)->loadAreaStats($this->record);

        return $infolist
            ->schema([
                Section::make('معلومات المنطقة')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم المنطقة'),
                        TextEntry::make('orders_count')
                            ->label('عدد الطلبات'),
                        TextEntry::make('total_sales')
                            ->label('إجمالي المبيعات')
                            ->money('EGP'),
                        TextEntry::make('total_profit')
                            ->label('إجمالي صافي الأرباح')
                            ->money('EGP'),
                        TextEntry::make('total_returns')
                            ->label('إجمالي المرتجعات')
                            ->money('EGP'),
                        TextEntry::make('total_cancelled')
                            ->label('إجمالي الملغية')
                            ->money('EGP'),
                    ])
                    ->columns(3)
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AreaOrdersChart::make([
                'record' => $this->record,
            ]),
            AreaReturnsChart::make([
                'record' => $this->record,
            ]),
            AreaCancelledOrdersChart::make([
                'record' => $this->record,
            ]),
        ];
    }
}
