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

if (!function_exists('printPdfAction')) {
    function printPdfAction($action)
    {
        return $action
            ->label('طباعة PDF')
            ->icon('heroicon-o-document-text')
            ->url(fn($record) => route('print.pdf', ['model' => get_class($record), 'id' => $record->id]))
            ->openUrlInNewTab()
            ->visible(fn($record) => $record->items->count() > 0)
            ->color('primary');
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
            new \App\Notifications\Templates\OrderStatusTemplate(),
            [
                'order_id' => $order->id,
                'order_code' => $order->id,
                'status' => $order->status->value
            ]
        );
    }
}

if (!function_exists('notifyCustomerWithReturnOrderStatus')) {
    function notifyCustomerWithReturnOrderStatus($returnOrder, $status): void
    {
        app(\App\Services\NotificationService::class)->sendToUser(
            $returnOrder->customer,
            new \App\Notifications\Templates\ReturnOrderStatusTemplate(),
            [
                'order_id' => $returnOrder->id,
                'order_code' => $returnOrder->id,
                'status' => $status
            ]
        );
    }
}

if (!function_exists('fetchAndSaveImageFromUrl')) {
    /**
     * Fetch image from URL and save it to storage
     *
     * @param string $imageUrl The URL of the image to fetch
     * @param string $identifier A unique identifier for the image (barcode or other)
     * @param string $directory The directory to save the image in
     * @return string|null The path where the image was saved or null if failed
     */
    function fetchAndSaveImageFromUrl(string $imageUrl, string $identifier, string $directory = 'products'): ?string
    {
        if (empty($imageUrl) || !str_starts_with($imageUrl, 'https')) {
            return null;
        }

        try {
            $filename = \Illuminate\Support\Str::slug($identifier ?? time()) . '-' . time() . '.jpg';
            $savePath = $directory . '/' . $filename;

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'PostmanRuntime/7.43.0',
                    'Accept' => '*/*',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept-Language' => 'en-US,en;q=0.9,ar-EG;q=0.8,ar;q=0.7'
                ])
                ->get($imageUrl);

            if ($response->successful()) {
                \Illuminate\Support\Facades\Storage::disk('public')->put($savePath, $response->body());
                return $savePath;
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }
}
