<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Main extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.main';

    protected static ?string $title = 'الصفحة الرئيسية';

    public static function canView(): bool
    {
        return true;
    }
}
