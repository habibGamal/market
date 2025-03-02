<?php

namespace App\Filament\Resources\Reports\CategoriesReportResource\Pages;

use App\Filament\Resources\Reports\CategoriesReportResource;
use App\Filament\Widgets\CategorySalesChart;
use App\Filament\Widgets\CategoryReturnsChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoriesReport extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = CategoriesReportResource::class;

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

    protected function getHeaderWidgets(): array
    {
        return [
            CategorySalesChart::make([
                'record' => $this->record,
            ]),
            CategoryReturnsChart::make([
                'record' => $this->record,
            ]),
        ];
    }
}
