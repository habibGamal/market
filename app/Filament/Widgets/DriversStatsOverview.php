<?php

namespace App\Filament\Widgets;

use App\Enums\DriverStatus;
use App\Enums\OrderStatus;
use App\Enums\ReturnOrderStatus;
use App\Filament\Resources\Reports\DriversReportResource\Pages\ListDriversReports;
use App\Models\Driver;
use App\Models\DriverTask;
use App\Models\Order;
use App\Models\ReturnOrderItem;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DriversStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListDriversReports::class;
    }

    protected function getStats(): array
    {
        $driversBalanceTotal = \DB::table('driver_accounts')
            ->sum('balance');

        $pendingOrdersCount = DriverTask::where('status', [DriverStatus::RECEIVED])
            ->count();


        $pendingOrdersTotal = Order::where('status', [OrderStatus::OUT_FOR_DELIVERY])
            ->sum('total');

        $driverReturnedProducts = \DB::table('driver_returned_products as rt')
            ->selectRaw('SUM((rt.packets_quantity + rt.piece_quantity/ products.packet_to_piece) * products.packet_cost) as total')
            ->join('products', 'rt.product_id', '=', 'products.id')
            ->get()
            ->first()->total;

        return [
            Stat::make('رصيد السائقين', number_format($driversBalanceTotal, 2) . ' جنيه')
                ->description('إجمالي رصيد السائقين')
                ->icon('heroicon-o-banknotes'),

            Stat::make('الطلبات قيد التسليم', $pendingOrdersCount)
                ->description('إجمالي عدد الطلبات مع السائقين التي لم يتم تسليمها')
                ->icon('heroicon-o-truck'),

            Stat::make('قيمة الطلبات قيد التسليم', number_format($pendingOrdersTotal, 2) . ' جنيه')
                ->description('إجمالي قيمة الطلبات التي لم يتم تسليمها')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('قيمة المرتجعات مع السائقين', number_format($driverReturnedProducts, 2) . ' جنيه')
                ->description('إجمالي قيمة المرتجعات التي لم تسلم للمخازن (بسعر التكلفة)')
                ->icon('heroicon-o-arrow-path'),
        ];
    }
}
