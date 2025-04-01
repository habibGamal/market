<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Enums\ReturnOrderStatus;
use App\Filament\Resources\OrderResource;
use App\Services\OrderServices;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('return_all_items')
                ->label('إرجاع جميع الأصناف')
                ->color('danger')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn($record) => $record->status === OrderStatus::DELIVERED && $record->discount > 0)
                ->requiresConfirmation()
                ->modalHeading('إرجاع جميع الأصناف')
                ->modalDescription('هل أنت متأكد من إرجاع جميع أصناف الطلب؟ لا يمكن التراجع عن هذا الإجراء.')
                ->modalSubmitActionLabel('إرجاع')
                ->action(function ($record) {
                    DB::transaction(function () use ($record) {
                        // Get all items from the order
                        $itemsToReturn = $record->items->map(function ($orderItem) {
                            return [
                                'order_item' => $orderItem,
                                'product_id' => $orderItem->product_id,
                                'packets_quantity' => $orderItem->packets_quantity,
                                'packet_price' => $orderItem->packet_price,
                                'packet_cost' => $orderItem->packet_cost,
                                'piece_quantity' => $orderItem->piece_quantity,
                                'piece_price' => $orderItem->piece_price,
                                'return_reason' => 'إرجاع كامل الطلب',
                                'notes' => 'تم إرجاع جميع الأصناف',
                                'status' => ReturnOrderStatus::PENDING,
                            ];
                        })->toArray();

                        // Return all items
                        if (count($itemsToReturn) > 0) {
                            app(OrderServices::class)->returnItems($record, $itemsToReturn);

                            // make discount value = 0 so that netTotal don't be negative
                            $record->update(['discount' => 0]);


                            notifyCustomerWithReturnOrderStatus($record, ReturnOrderStatus::PENDING->value);

                            Notification::make()
                                ->title('تم إرجاع جميع الأصناف بنجاح')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('لا توجد أصناف لإرجاعها')
                                ->warning()
                                ->send();
                        }
                    });
                }),
        ];
    }
}
