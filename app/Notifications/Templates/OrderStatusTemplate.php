<?php
namespace App\Notifications\Templates;

use App\Enums\OrderStatus;

class OrderStatusTemplate extends BaseTemplate
{
    /**
     * Get the type of notification
     *
     * @return string
     */
    public function getType(): string
    {
        return 'status';
    }

    /**
     * Get the notification title
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getTitle(array $data = []): string
    {
        return $data['title'] ?? 'تحديث حالة الطلب';
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
        $status = $data['status'] ?? OrderStatus::PENDING->value;

        return match ($status) {
            OrderStatus::PENDING->value => "طلبك رقم #{$orderCode} قيد الانتظار للمراجعة",
            OrderStatus::CANCELLED->value => "تم إلغاء طلبك رقم #{$orderCode}",
            OrderStatus::PREPARING->value => "جاري تحضير طلبك رقم #{$orderCode}",
            OrderStatus::OUT_FOR_DELIVERY->value => "طلبك رقم #{$orderCode} في الطريق إليك",
            OrderStatus::DELIVERED->value => "تم توصيل طلبك رقم #{$orderCode} بنجاح",
            default => "تم تحديث حالة الطلب رقم #{$orderCode}",
        };
    }

    /**
     * Get the notification icon path
     *
     * @return string
     */
    public function getIcon(): string
    {
        return '/images/notifications/status.png';
    }

    /**
     * Get the action text for the notification
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'تفاصيل الطلب';
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
