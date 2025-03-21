<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\NotificationManager;
use App\Notifications\Templates\GeneralTemplate;
use App\Services\NotificationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCustomerNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected NotificationManager $notification,
        protected Customer $customer
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        // Skip processing if the batch has been cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        try {
            $notificationService->sendToUser(
                $this->customer,
                new GeneralTemplate(),
                array_merge($this->notification->data ?? [], [
                    'title' => $this->notification->title,
                    'body' => $this->notification->body,
                    'type' => $this->notification->notification_type->value,
                    'notification_id' => $this->notification->id,
                    'action_url' => $this->notification->data['action_url'] ?? null,
                    'action_text' => !empty($this->notification->data['action_url']) ? 'عرض المزيد' : null,
                ])
            );

            $this->notification->increment('successful_sent');
        } catch (\Exception $e) {
            $this->notification->increment('failed_sent');

            $currentLog = $this->notification->error_log ? json_decode($this->notification->error_log, true) : [];
            $currentLog[] = [
                'customer_id' => $this->customer->id,
                'error' => $e->getMessage(),
                'time' => now()->toDateTimeString(),
            ];
            $this->notification->update(['error_log' => json_encode($currentLog)]);

            // Throw the exception to mark the job as failed in the batch
            throw $e;
        }
    }
}
