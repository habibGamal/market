<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\Pages;

use App\Filament\Resources\Reports\ProductsReportResource;
use App\Filament\Widgets\ProductSalesChart;
use App\Filament\Widgets\ProductReturnsChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewProductsReport extends ViewRecord
{
    use ReportsFilter;
    protected static string $resource = ProductsReportResource::class;


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
            ProductSalesChart::make([
                'record' => $this->record,
            ]),
            ProductReturnsChart::make([
                'record' => $this->record,
            ]),
        ];
    }
}
