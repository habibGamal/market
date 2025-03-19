<?php
namespace App\Notifications\Templates;

class OrderItemsCancelledTemplate extends BaseTemplate
{
    /**
     * Get the type of notification
     *
     * @return string
     */
    public function getType(): string
    {
        return 'order-items-cancelled';
    }

    /**
     * Get the notification title
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getTitle(array $data = []): string
    {
        return 'تم إلغاء بعض المنتجات من طلبك';
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
        $itemsCount = $data['items_count'] ?? 0;
        return "تم إلغاء {$itemsCount} منتج من طلبك رقم #{$orderCode}";
    }

    /**
     * Get the notification icon path
     *
     * @return string
     */
    public function getIcon(): string
    {
        return '/images/notifications/order-cancelled.png';
    }

    /**
     * Get the action text for the notification
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'عرض التفاصيل';
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
