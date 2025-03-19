<?php

namespace App\Http\Controllers;

use App\Notifications\Templates\GeneralTemplate;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        $notifications = $customer->notifications()->latest()->get()->map(function ($notification) {
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
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = auth('customer')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        auth('customer')->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
