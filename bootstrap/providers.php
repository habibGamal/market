<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\DriverPanelProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    App\Providers\SettingsServiceProvider::class,
    Laravel\Scout\ScoutServiceProvider::class,
    TeamTNT\Scout\TNTSearchScoutServiceProvider::class,
];
