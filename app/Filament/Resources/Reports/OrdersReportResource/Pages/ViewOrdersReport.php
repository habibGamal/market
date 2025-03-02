<?php

namespace App\Filament\Resources\Reports\OrdersReportResource\Pages;

use App\Filament\Resources\Reports\OrdersReportResource;
use App\Filament\Widgets\OrderItemsChart;
use App\Filament\Widgets\ReturnItemsChart;
use App\Filament\Widgets\CancelledItemsChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewOrdersReport extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = OrdersReportResource::class;

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
        return $infolist
            ->schema([
                Section::make('تفاصيل العميل')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('id')
                            ->label('رقم العميل'),
                        TextEntry::make('name')
                            ->label('اسم العميل'),
                        TextEntry::make('area.name')
                            ->label('المنطقة'),
                        TextEntry::make('rating_points')
                            ->label('نقاط التقييم'),
                        TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime('Y-m-d H:i:s'),
                    ])
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderItemsChart::make([
                'record' => $this->record,
            ]),
            ReturnItemsChart::make([
                'record' => $this->record,
            ]),
            CancelledItemsChart::make([
                'record' => $this->record,
            ]),
        ];
    }
}
