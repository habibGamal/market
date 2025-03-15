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
    protected static string $resource = OrdersReportResource::class;

}
