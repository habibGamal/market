<?php
namespace App\Notifications\Templates;

class GeneralTemplate extends BaseTemplate
{
    /**
     * Get the type of notification
     *
     * @return string
     */
    public function getType(): string
    {
        return 'general';
    }

    /**
     * Get the notification title
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getTitle(array $data = []): string
    {
        return $data['title'] ?? 'إشعار جديد';
    }

    /**
     * Get the notification body
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getBody(array $data = []): string
    {
        return $data['description'] ?? $data['body'] ?? 'لديك إشعار جديد. انقر لعرض التفاصيل.';
    }

    /**
     * Get the notification icon path
     *
     * @return string
     */
    public function getIcon(): string
    {
        return '/images/notifications/general.png';
    }

    /**
     * Get the action text for the notification
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getActionText(array $data = []): string
    {
        return $data['action_text'] ?? 'عرض';
    }

    /**
     * Get the action URL for the notification
     *
     * @param array $data Additional context data
     * @return string
     */
    public function getActionUrl(array $data = []): string
    {
        return $data['url'] ?? $data['action_url'] ?? '/notifications';
    }
}
