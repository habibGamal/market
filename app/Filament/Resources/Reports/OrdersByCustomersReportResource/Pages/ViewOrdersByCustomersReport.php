<?php

namespace App\Filament\Resources\Reports\OrdersByCustomersReportResource\Pages;

use App\Filament\Resources\Reports\OrdersByCustomersReportResource;
use App\Filament\Resources\Reports\OrdersByCustomersReportResource\RelationManagers;
use App\Filament\Widgets\CancelledItemsChart;
use App\Filament\Widgets\OrderItemsChart;
use App\Filament\Widgets\ReturnItemsChart;
use App\Services\Reports\OrdersByCustomersReportService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Traits\ReportsFilter;
use Filament\Actions;

class ViewOrdersByCustomersReport extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = OrdersByCustomersReportResource::class;


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
        $this->record = app(OrdersByCustomersReportService::class)->loadCustomerStats($this->record);

        return $infolist
            ->schema([
                Section::make('بيانات العميل')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم العميل'),
                        TextEntry::make('phone')
                            ->label('رقم الهاتف'),
                        TextEntry::make('whatsapp')
                            ->label('رقم الواتساب'),
                        TextEntry::make('area.name')
                            ->label('المنطقة'),
                        TextEntry::make('address')
                            ->label('العنوان')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('إحصائيات الطلبات')
                    ->schema([
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

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\ReturnOrderItemsRelationManager::class,
            RelationManagers\CancelledOrderItemsRelationManager::class,
        ];
    }
}
