<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class Notify extends Notification
{
    protected $title;
    protected $body;
    protected $icon;
    protected $action;
    protected $actionUrl;
    protected $data;

    public function __construct($title, $body, $icon = '/approved-icon.png', $action = null, $actionUrl = null, $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->icon = $icon;
        $this->action = $action;
        $this->actionUrl = $actionUrl;
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $webPushMessage = (new WebPushMessage)
            ->title($this->title)
            ->icon($this->icon)
            ->body($this->body)
            ->options(['TTL' => 1000]);

        if ($this->action && $this->actionUrl) {
            $webPushMessage->action($this->action, $this->actionUrl);
        }

        if (!empty($this->data)) {
            $webPushMessage->data($this->data);
        }


        return $webPushMessage;
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'action_text' => $this->action,
            'action_url' => $this->actionUrl,
            'data' => $this->data
        ]);
    }
}
