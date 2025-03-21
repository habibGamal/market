<?php

namespace App\Jobs;

use App\Models\NotificationManager;
use App\Services\NotificationManagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected NotificationManager $notification
    ) {}

    public function handle(NotificationManagerService $service): void
    {
        $service->processNotification($this->notification);
    }
}
