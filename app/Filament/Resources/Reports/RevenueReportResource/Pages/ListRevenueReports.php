<?php

namespace App\Filament\Resources\Reports\RevenueReportResource\Pages;

use App\Filament\Resources\Reports\RevenueReportResource;
use App\Filament\Widgets\RevenueStatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListRevenueReports extends ListRecords
{
    use ExposesTableToWidgets, ReportsFilter;

    protected static string $resource = RevenueReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filter')
                ->label('تحديد الفترة')
                ->form(static::filtersForm())
                ->action(function (array $data): void {
                    if ($data['period'] !== static::PERIOD_CUSTOM) {
                        $range = static::getRange($data['period']);
                        $this->dispatch('updateWidgets', filterFormData: [
                            'start_date' => $range['start_date'],
                            'end_date' => $range['end_date'],
                            'period' => $data['period'],
                        ]);
                    } else {
                        $this->dispatch('updateWidgets', filterFormData: $data);
                    }
                })
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueStatsOverview::class,
            RevenueChart::class,
        ];
    }
}
