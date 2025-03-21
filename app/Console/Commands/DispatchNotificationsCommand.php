<?php

namespace App\Console\Commands;

use App\Services\NotificationDispatcherService;
use Illuminate\Console\Command;

class DispatchNotificationsCommand extends Command
{
    protected $signature = 'notifications:dispatch';
    protected $description = 'Dispatch pending notifications that are scheduled for now or in the past';

    public function handle(NotificationDispatcherService $dispatcher): int
    {
        $this->info('Dispatching pending notifications...');

        try {
            $dispatcher->dispatchPendingNotifications();
            $this->info('Notifications dispatched successfully.');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error dispatching notifications: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
