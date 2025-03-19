<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\NotificationService;
use App\Notifications\Templates\StatusTemplate;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Order $order): void
    {
        // dd($order->isDirty('status'),$order->status);
        // Check if status was changed
        if ($order->isDirty('status')) {
            app(NotificationService::class)->sendToUser(
                $order->customer,
                new StatusTemplate(),
                [
                    'order_id' => $order->id,
                    'order_code' => $order->id,
                    'status' => $order->status->value
                ]
            );
        }
    }
}
