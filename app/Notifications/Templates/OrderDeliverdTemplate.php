<?php
namespace App\Notifications\Templates;

class OrderDeliverdTemplate extends BaseTemplate
{
    /**
     * Get the type of notification
     *
     * @return string
     */
    public function getType(): string
    {
        return 'delivery';
    }

    /**
     * Get the notification title
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getTitle(array $data = []): string
    {
        return 'تم توصيل طلبك';
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
        return "تم توصيل طلبك رقم #{$orderCode} بنجاح. نتمنى لك تجربة طيبة مع منتجاتنا.";
    }

    /**
     * Get the notification icon path
     *
     * @return string
     */
    public function getIcon(): string
    {
        return '/images/notifications/delivery.png';
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
        return "/orders/{$orderId}";
    }
}
