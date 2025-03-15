<?php

namespace App\Console\Commands;

use App\Filament\Resources\Reports\ProductExpirationReportResource;
use App\Models\User;
use App\Notifications\ProductExpirationNotification;
use App\Services\Reports\ProductExpirationReportService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckProductExpirations extends Command
{
    protected $signature = 'app:check-product-expirations';
    protected $description = 'Check for products that just exceeded their half expiration period';

    public function handle(): void
    {
        $reportService = new ProductExpirationReportService();

        // Get products that just exceeded half period (today's date matches the half period date)
        $products = $reportService->getProductsWithExpirationInfo()
            ->get();

        if ($products->isNotEmpty()) {
            // Get admin users
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->get();
            Notification::make()
                ->warning()
                ->title('منتجات تجاوزت نصف مدة الصلاحية')
                ->body('يوجد ' . $products->count() . ' منتجات تجاوزت نصف مدة صلاحيتها')
                ->actions([
                    Action::make('display_report')
                        ->label('عرض التقرير')
                        ->url(ProductExpirationReportResource::getUrl('index')),
                ])
                ->sendToDatabase($admins, isEventDispatched: true);
        }
    }
}
