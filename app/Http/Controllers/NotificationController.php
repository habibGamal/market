<?php

namespace App\Http\Controllers;

use App\Models\NotificationManager;
use App\Notifications\Templates\GeneralTemplate;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the user's notifications.
     */
    public function index(): Response
    {
        $customer = auth('customer')->user();
        $notifications = $customer->notifications()
            ->latest()
            ->paginate(10)
            ->through(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['data']['type'] ?? 'general',
                    'title' => $notification->data['title'] ?? '',
                    'description' => $notification->data['body'] ?? '',
                    'date' => $notification->created_at->toISOString(),
                    'isRead' => $notification->read_at !== null,
                    'data' => $notification->data,
                ];
            });

        return Inertia::render('Notifications/Index', [
            'notifications' => inertia()->merge(
                $notifications->items()
            ),
            'pagination' => Arr::except($notifications->toArray(), ['data']),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = auth('customer')->user()->notifications()->findOrFail($id);

        // Check if it's a notification manager notification
        if ($this->isNotificationManagerNotification($notification)) {
            $this->handleNotificationManagerRead($notification);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $customer = auth('customer')->user();

        // Handle notification manager notifications first
        $this->handleNotificationManagerBulkRead($customer);

        // Mark all as read
        $customer->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Track notification click (only for notification manager notifications).
     */
    public function trackClick(Request $request, string $id)
    {
        $notification = auth('customer')->user()->notifications()->findOrFail($id);

        if (!$this->isNotificationManagerNotification($notification)) {
            return response()->json(['error' => 'Invalid notification type'], 400);
        }

        if ($notificationManager = $this->getNotificationManager($notification)) {
            $notificationManager->incrementClickCount();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Check if notification is from notification manager.
     */
    private function isNotificationManagerNotification($notification): bool
    {
        return !empty($notification->data['data']['notification_id']);
    }

    /**
     * Get NotificationManager instance if exists.
     */
    private function getNotificationManager($notification): ?NotificationManager
    {
        if (!$this->isNotificationManagerNotification($notification)) {
            return null;
        }

        return NotificationManager::query()
            ->where('id', $notification->data['data']['notification_id'])
            ->first();
    }

    /**
     * Handle read status for notification manager notification.
     */
    private function handleNotificationManagerRead($notification): void
    {
        if (!$notification->read_at) {
            if ($notificationManager = $this->getNotificationManager($notification)) {
                $notificationManager->incrementReadCount();
            }
        }
    }

    /**
     * Handle bulk read for notification manager notifications.
     */
    private function handleNotificationManagerBulkRead($customer): void
    {
        // Get unread notifications with notification_id
        $unreadNotifications = $customer->unreadNotifications()
            ->whereNotNull('data->data->notification_id')
            ->get();

        // Increment read count for each NotificationManager
        $unreadNotifications->each(function ($notification) {
            if ($notificationManager = $this->getNotificationManager($notification)) {
                $notificationManager->incrementReadCount();
            }
        });
    }
}
