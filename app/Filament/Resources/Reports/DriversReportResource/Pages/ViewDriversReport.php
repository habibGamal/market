<?php

namespace App\Filament\Resources\Reports\DriversReportResource\Pages;

use App\Filament\Resources\Reports\DriversReportResource;
use App\Filament\Widgets\DriverDeliveriesChart;
use App\Filament\Widgets\DriverReturnsChart;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use App\Filament\Resources\Reports\DriversReportResource\RelationManagers;

class ViewDriversReport extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = DriversReportResource::class;

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
                Section::make('معلومات السائق')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('id')
                            ->label('رقم السائق'),
                        TextEntry::make('name')
                            ->label('اسم السائق'),
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني'),
                        TextEntry::make('account.balance')
                            ->label('الرصيد')
                            ->money('EGP'),
                        TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime('Y-m-d H:i:s'),
                    ])
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DriverDeliveriesChart::make([
                'record' => $this->record,
            ]),
            DriverReturnsChart::make([
                'record' => $this->record,
            ]),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\ReturnedProductsRelationManager::class,
        ];
    }

}
