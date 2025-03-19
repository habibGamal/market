<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SettingKey: string implements HasColor, HasIcon, HasLabel
{
    // System Settings
    case APP_NAME = 'app_name';
    case APP_ICON = 'app_icon';
    case APP_LOGO = 'app_logo';

    // Order Settings
    case MIN_TOTAL_ORDER = 'min_total_order';
    case RATING_POINTS_PERCENT = 'rating_points_percent';

    public function getColor(): ?string
    {
        return match ($this) {
            self::APP_NAME, self::APP_ICON, self::APP_LOGO => 'blue',
            self::MIN_TOTAL_ORDER, self::RATING_POINTS_PERCENT => 'amber',
            default => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::APP_NAME => 'heroicon-o-building-office',
            self::APP_ICON => 'heroicon-o-photo',
            self::APP_LOGO => 'heroicon-o-photo',
            self::MIN_TOTAL_ORDER => 'heroicon-o-currency-dollar',
            self::RATING_POINTS_PERCENT => 'heroicon-o-star',
            default => 'heroicon-o-cog',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::APP_NAME => 'اسم التطبيق',
            self::APP_ICON => 'أيقونة التطبيق',
            self::APP_LOGO => 'شعار التطبيق',
            self::MIN_TOTAL_ORDER => 'الحد الأدنى لإجمالي الطلب',
            self::RATING_POINTS_PERCENT => 'نسبة نقاط التقييم',
        };
    }

    public function getType(): string
    {
        return match ($this) {
            self::APP_NAME => 'string',
            self::APP_ICON, self::APP_LOGO => 'image',
            self::MIN_TOTAL_ORDER => 'float',
            self::RATING_POINTS_PERCENT => 'float',
        };
    }

    public function getGroup(): string
    {
        return match ($this) {
            self::APP_NAME, self::APP_ICON, self::APP_LOGO => 'system',
            self::MIN_TOTAL_ORDER, self::RATING_POINTS_PERCENT => 'orders',
            default => 'general',
        };
    }

    public static function getGroups(): array
    {
        return [
            'system' => 'إعدادات النظام',
            'orders' => 'إعدادات الطلبات',
            'invoices' => 'إعدادات الفواتير',
            'notifications' => 'إعدادات الإشعارات',
        ];
    }
}
