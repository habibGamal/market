<?php

namespace App\Http\Middleware;

use App\Enums\SettingKey;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user('customer');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'cartCount' => $user ? $user->cart->items()->count() : 0,
            'notificationsCount' => $user ? $user->unreadNotifications()->count() : 0,
            'appIcon' => settings(SettingKey::APP_LOGO),
        ];
    }
}
