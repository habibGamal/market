<?php

/*
 * Here you can define your own helper functions.
 * Make sure to use the `function_exists` check to not declare the function twice.
 */

if (!function_exists('example')) {
    function example(): string
    {
        return 'This is an example function you can use in your project.';
    }
}

if (!function_exists('printTemplate')) {
    function printTemplate(): \App\PrintTemplate
    {
        return new \App\PrintTemplate();
    }
}


if (!function_exists('printAction')) {
    function printAction($action)
    {
        return $action
            ->label('طباعة')
            ->icon('heroicon-o-printer')
            // ->url(fn() => route('print', ['model' => get_class($this->record), 'id' => $this->record->id]))
            ->url(fn($record) => route('print', ['model' => get_class($record), 'id' => $record->id]))
            ->openUrlInNewTab()
            ->visible(fn($record) => $record->items->count() > 0)
            ->color('gray');
    }
}

if (!function_exists('settings')) {
    /**
     * Access the settings service.
     *
     * @param string|\App\Enums\SettingKey|null $key
     * @param mixed $default
     * @return mixed|\App\Services\SettingsService
     */
    function settings(string|\App\Enums\SettingKey $key = null, mixed $default = null): mixed
    {
        $settings = app('settings');

        if ($key === null) {
            return $settings;
        }

        return $settings->get($key, $default);
    }
}


if (!function_exists('notifyCustomerWithOrderStatus')) {
    function notifyCustomerWithOrderStatus($order): void
    {
        app(\App\Services\NotificationService::class)->sendToUser(
            $order->customer,
            new \App\Notifications\Templates\StatusTemplate(),
            [
                'order_id' => $order->id,
                'order_code' => $order->id,
                'status' => $order->status->value
            ]
        );
    }
}
