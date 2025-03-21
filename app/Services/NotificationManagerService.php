<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\NotificationManager;
use App\Models\Order;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Jobs\ProcessCustomerNotificationJob;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Throwable;

class NotificationManagerService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function processNotification(NotificationManager $notification, ?Collection $testCustomers = null): void
    {
        try {
            $notification->update(['status' => NotificationStatus::PROCESSING]);

            $customers = $testCustomers ?? $this->getFilteredCustomers($notification->filters ?? []);

            $notification->update(['total_recipients' => $customers->count()]);

            $jobs = $customers->map(fn ($customer) => new ProcessCustomerNotificationJob($notification, $customer));

            Bus::batch($jobs)
                ->name("Notification {$notification->id}")
                ->allowFailures()
                ->onQueue('notifications')
                ->then(function (Batch $batch) use ($notification) {
                    // All jobs completed successfully
                    $notification->update([
                        'status' => NotificationStatus::COMPLETED,
                        'sent_at' => now(),
                    ]);
                })
                ->catch(function (Batch $batch, Throwable $e) use ($notification) {
                    // First batch job failure
                    $notification->update([
                        'status' => NotificationStatus::FAILED,
                        'error_log' => json_encode([
                            'error' => $e->getMessage(),
                            'time' => now()->toDateTimeString(),
                        ]),
                    ]);
                })
                ->finally(function (Batch $batch) use ($notification) {
                    // Batch completed, but some jobs might have failed
                    if ($batch->failedJobs > 0 && $notification->status !== NotificationStatus::FAILED) {
                        $notification->update([
                            'status' => NotificationStatus::COMPLETED,
                            'sent_at' => now(),
                        ]);
                    }
                })
                ->dispatch();

        } catch (\Exception $e) {
            $notification->update([
                'status' => NotificationStatus::FAILED,
                'error_log' => json_encode([
                    'error' => $e->getMessage(),
                    'time' => now()->toDateTimeString(),
                ]),
            ]);
            throw $e;
        }
    }

    protected function getFilteredCustomers(array $filters): Collection
    {
        $query = Customer::query();

        // Location filters
        if (!empty($filters['governorates'])) {
            $query->whereIn('gov_id', $filters['governorates']);
        }

        if (!empty($filters['cities'])) {
            $query->whereIn('city_id', $filters['cities']);
        }

        if (!empty($filters['areas'])) {
            $query->whereIn('area_id', $filters['areas']);
        }

        // Business type and status filters
        if (!empty($filters['business_types'])) {
            $query->whereIn('business_type_id', $filters['business_types']);
        }

        if (!empty($filters['active_only'])) {
            $query->where('blocked', false);
        }

        // Rating and points filters
        if (isset($filters['min_rating'])) {
            $query->where('rating_points', '>=', $filters['min_rating']);
        }

        if (isset($filters['max_rating'])) {
            $query->where('rating_points', '<=', $filters['max_rating']);
        }

        if (isset($filters['min_points'])) {
            $query->where('points', '>=', $filters['min_points']);
        }

        if (isset($filters['max_points'])) {
            $query->where('points', '<=', $filters['max_points']);
        }

        // Orders filter
        if (!empty($filters['has_orders'])) {
            $query->whereHas('orders');
        }

        return $query->get();
    }

    protected function logError(NotificationManager $notification, string $error, int $customerId): void
    {
        $currentLog = $notification->error_log ? json_decode($notification->error_log, true) : [];
        $currentLog[] = [
            'customer_id' => $customerId,
            'error' => $error,
            'time' => now()->toDateTimeString(),
        ];
        $notification->update(['error_log' => json_encode($currentLog)]);
    }
}
