<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Jobs\ProcessNotificationJob;
use App\Models\NotificationManager;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NotificationDispatcherService
{
    public function dispatchPendingNotifications(): void
    {
        $this->getPendingNotifications()
            ->each(function (NotificationManager $notification) {
                ProcessNotificationJob::dispatch($notification);
                $notification->update(['status' => NotificationStatus::PROCESSING]);
            });
    }

    public function scheduleNotification(NotificationManager $notification): void
    {
        if (!$notification->schedule_at || $notification->schedule_at->isPast()) {
            ProcessNotificationJob::dispatch($notification);
            return;
        }

        ProcessNotificationJob::dispatch($notification)
            ->delay($notification->schedule_at);
    }

    protected function getPendingNotifications(): Collection
    {
        return NotificationManager::query()
            ->where('status', NotificationStatus::PENDING)
            ->where(function ($query) {
                $query->whereNull('schedule_at')
                    ->orWhere('schedule_at', '<=', Carbon::now());
            })
            ->get();
    }
}
