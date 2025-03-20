<?php

namespace App\Notifications\Templates;

use App\Enums\ReturnOrderStatus;

class ReturnOrderStatusTemplate extends BaseTemplate
{
    /**
     * Get the type of notification
     *
     * @return string
     */
    public function getType(): string
    {
        return 'return-status';
    }

    /**
     * Get the notification title
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getTitle(array $data = []): string
    {
        return $data['title'] ?? 'تحديث حالة طلب الإرجاع';
    }

    /**
     * Get the notification body
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getBody(array $data = []): string
    {
        $orderCode = $data['order_code'] ?? '';
        $status = $data['status'] ?? ReturnOrderStatus::PENDING->value;

        return match ($status) {
            ReturnOrderStatus::PENDING->value => "طلب الإرجاع رقم #{$orderCode} قيد المراجعة",
            ReturnOrderStatus::DRIVER_PICKUP->value => "سيقوم السائق باستلام طلب الإرجاع رقم #{$orderCode} قريباً",
            ReturnOrderStatus::RECEIVED_FROM_CUSTOMER->value => "تم استلام طلب الإرجاع رقم #{$orderCode} بنجاح",
            default => "تم تحديث حالة طلب الإرجاع رقم #{$orderCode}",
        };
    }

    /**
     * Get the notification icon path
     *
     * @return string
     */
    public function getIcon(): string
    {
        return '/images/notifications/return-status.png';
    }

    /**
     * Get the action text for the notification
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'تفاصيل طلب الإرجاع';
    }

    /**
     * Get the action URL for the notification
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getActionUrl(array $data = []): string
    {
        $orderId = $data['order_id'] ?? '';
        return route('orders.show', $orderId);
    }
}
