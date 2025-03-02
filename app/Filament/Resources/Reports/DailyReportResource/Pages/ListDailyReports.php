<?php

namespace App\Filament\Resources\Reports\DailyReportResource\Pages;

use App\Filament\Resources\Reports\DailyReportResource;
use App\Filament\Widgets\DailyReportStats;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;

class ListDailyReports extends ListRecords
{

    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('select_date')
                ->label('اختيار تاريخ')
                ->form([
                    DatePicker::make('date')
                        ->label('التاريخ')
                        ->format('Y-m-d')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->dispatch('updateWidgets', date: $data['date']);
                })
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailyReportStats::class,
        ];
    }
}
