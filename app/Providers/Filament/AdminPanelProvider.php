<?php

namespace App\Providers\Filament;

use App\Enums\SettingKey;
use App\Filament\Pages\App\Profile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\CustomActivityLogResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        try {
            $brandName = settings(SettingKey::APP_NAME, 'Sindbad');
        } catch (\Exception $e) {
            $brandName = 'Sindbad';
        }
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName($brandName)
            ->passwordReset()
            ->sidebarCollapsibleOnDesktop()
            //            ->sidebarFullyCollapsibleOnDesktop()
            // ->spa()
            ->profile(Profile::class, false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->globalSearch(CustomGlobalSearchProvider::class)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldKeyBindingSuffix()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                'إدارة المنتجات',
                'إدارة المشتريات',
                'إدارة المخزن',
                'إدارة الحسابات',
                'إدارة المبيعات',
                'إدارة النظام',
                'إدارة الوصول',
                'التقارير',
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                ActivitylogPlugin::make()
                    ->label('سجل')
                    ->pluralLabel('السجلات')
                    ->navigationGroup('إدارة النظام')
                    ->resource(CustomActivityLogResource::class)
                    ->authorize(
                        fn() => auth()->user()->can('view_any_custom::activity::log')
                    ),
            ])->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn(): string => \Illuminate\Support\Facades\Blade::render('@livewire(\App\Livewire\TopbarContent::class)'),
            );
    }
}

