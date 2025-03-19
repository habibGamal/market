<?php

namespace App\Services;

use App\Models\Customer;
use App\Notifications\Notify;
use App\Notifications\Templates\BaseTemplate;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send notification to all users
     *
     * @param BaseTemplate $template The notification template
     * @param array $data Additional data for the notification
     * @return void
     */
    public function sendToAll(BaseTemplate $template, array $data = []): void
    {
        Customer::chunk(100, function ($customers) use ($template, $data) {
            foreach ($customers as $customer) {
                $this->sendToUser($customer, $template, $data);
            }
        });
    }

    /**
     * Send notification to a list of users
     *
     * @param array|Collection $users Users to send notifications to
     * @param BaseTemplate $template The notification template
     * @param array $data Additional data for the notification
     * @return void
     */
    public function sendToMany($users, BaseTemplate $template, array $data = []): void
    {
        $users = $users instanceof Collection ? $users : collect($users);

        $users->chunk(100)->each(function ($chunk) use ($template, $data) {
            foreach ($chunk as $user) {
                $this->sendToUser($user, $template, $data);
            }
        });
    }

    /**
     * Send notification to a specific user
     *
     * @param Customer|int $user The user or user ID to send notification to
     * @param BaseTemplate $template The notification template
     * @param array $data Additional data for the notification
     * @return void
     */
    public function sendToUser($user, BaseTemplate $template, array $data = []): void
    {
        if (!$user instanceof Customer) {
            $user = Customer::find($user);
        }

        if (!$user) {
            return;
        }

        // Get template data
        $templateData = $template->getData($data);

        // Send the notification
        $user->notify(new Notify(
            $templateData['title'],
            $templateData['body'],
            $templateData['icon'],
            $templateData['action_text'],
            $templateData['action_url'],
            array_merge(['type' => $template->getType()], $data)
        ));
    }
}
