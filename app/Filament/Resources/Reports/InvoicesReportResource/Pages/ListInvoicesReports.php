<?php

namespace App\Filament\Resources\Reports\InvoicesReportResource\Pages;

use App\Filament\Resources\Reports\InvoicesReportResource;
use App\Filament\Widgets\InvoiceStatsChart;
use App\Filament\Widgets\PurchaseInvoiceStats;
use App\Filament\Widgets\ReturnPurchaseInvoiceStats;
use App\Filament\Widgets\WasteInvoiceStats;
use App\Traits\ReportsFilter;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListInvoicesReports extends ListRecords
{
    use ReportsFilter;

    protected static string $resource = InvoicesReportResource::class;

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
            PurchaseInvoiceStats::class,
            ReturnPurchaseInvoiceStats::class,
            WasteInvoiceStats::class,
            InvoiceStatsChart::class,
        ];
    }
}
