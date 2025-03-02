<?php

namespace App\Filament\Resources\Reports\BrandsReportResource\Pages;

use App\Filament\Resources\Reports\BrandsReportResource;
use App\Filament\Widgets\BrandSalesChart;
use App\Filament\Widgets\BrandReturnsChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewBrandsReport extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = BrandsReportResource::class;

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
            BrandSalesChart::make([
                'record' => $this->record,
            ]),
            BrandReturnsChart::make([
                'record' => $this->record,
            ]),
        ];
    }
}
