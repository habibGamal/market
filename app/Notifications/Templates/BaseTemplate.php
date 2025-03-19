<?php
namespace App\Notifications\Templates;

abstract class BaseTemplate
{
    /**
     * Get the type of notification
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Get the notification title
     *
     * @param array $data Additional context data
     * @return string
     */
    abstract public function getTitle(array $data = []): string;

    /**
     * Get the notification body
     *
     * @param array $data Additional context data
     * @return string
     */
    abstract public function getBody(array $data = []): string;

    /**
     * Get the notification icon path
     *
     * @return string
     */
    abstract public function getIcon(): string;

    /**
     * Get the action text for the notification
     *
     * @return string
     */
    abstract public function getActionText(): string;

    /**
     * Get the action URL for the notification
     *
     * @param array $data Additional context data
     * @return string
     */
    abstract public function getActionUrl(array $data = []): string;

    /**
     * Get all the notification data as an array
     *
     * @param array $data Additional context data
     * @return array
     */
    public function getData(array $data = []): array
    {
        return [
            'title' => $this->getTitle($data),
            'body' => $this->getBody($data),
            'icon' => $this->getIcon(),
            'action_text' => $this->getActionText(),
            'action_url' => $this->getActionUrl($data),
        ];
    }
}
