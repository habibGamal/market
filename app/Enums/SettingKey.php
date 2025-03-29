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
    case STOP_SELLING = 'stop_selling';

    case ORDER_RECEIPT_FOOTER = 'order_receipt_footer';

    // Integration Settings
    case WHATSAPP_SERVER_ENDPOINT = 'whatsapp_server_endpoint';

    // Support Settings
    case SUPPORT_PHONE = 'support_phone';
    case SUPPORT_EMAIL = 'support_email';
    case SUPPORT_ADDRESS = 'support_address';
    case SUPPORT_HOURS = 'support_hours';
    case SUPPORT_CHAT_HOURS = 'support_chat_hours';

    // Support Policies
    case PRIVACY_POLICY = 'privacy_policy';
    case SHIPPING_POLICY = 'shipping_policy';
    case RETURN_POLICY = 'return_policy';
    case PAYMENT_POLICY = 'payment_policy';

    public function getColor(): ?string
    {
        return match ($this) {
            self::APP_NAME, self::APP_ICON, self::APP_LOGO => 'blue',
            self::MIN_TOTAL_ORDER, self::RATING_POINTS_PERCENT => 'amber',
            self::STOP_SELLING => 'danger',
            self::WHATSAPP_SERVER_ENDPOINT => 'green',
            self::SUPPORT_PHONE, self::SUPPORT_EMAIL, self::SUPPORT_ADDRESS,
            self::SUPPORT_HOURS, self::SUPPORT_CHAT_HOURS => 'indigo',
            self::PRIVACY_POLICY, self::SHIPPING_POLICY,
            self::RETURN_POLICY, self::PAYMENT_POLICY => 'violet',
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
            self::STOP_SELLING => 'heroicon-o-no-symbol',
            self::ORDER_RECEIPT_FOOTER => 'heroicon-o-document-text',
            self::WHATSAPP_SERVER_ENDPOINT => 'heroicon-o-server',
            self::SUPPORT_PHONE => 'heroicon-o-phone',
            self::SUPPORT_EMAIL => 'heroicon-o-envelope',
            self::SUPPORT_ADDRESS => 'heroicon-o-map-pin',
            self::SUPPORT_HOURS => 'heroicon-o-clock',
            self::SUPPORT_CHAT_HOURS => 'heroicon-o-chat-bubble-left-right',
            self::PRIVACY_POLICY => 'heroicon-o-shield-check',
            self::SHIPPING_POLICY => 'heroicon-o-truck',
            self::RETURN_POLICY => 'heroicon-o-arrow-left',
            self::PAYMENT_POLICY => 'heroicon-o-credit-card',
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
            self::STOP_SELLING => 'إيقاف البيع',
            self::ORDER_RECEIPT_FOOTER => 'دباجة إيصال الطلب',
            self::WHATSAPP_SERVER_ENDPOINT => 'رابط خادم واتساب',
            self::SUPPORT_PHONE => 'رقم هاتف الدعم',
            self::SUPPORT_EMAIL => 'بريد إلكتروني للدعم',
            self::SUPPORT_ADDRESS => 'عنوان الشركة',
            self::SUPPORT_HOURS => 'ساعات العمل',
            self::SUPPORT_CHAT_HOURS => 'ساعات الدردشة المباشرة',
            self::PRIVACY_POLICY => 'سياسة الخصوصية',
            self::SHIPPING_POLICY => 'سياسة الشحن والتوصيل',
            self::RETURN_POLICY => 'سياسة الإرجاع والاستبدال',
            self::PAYMENT_POLICY => 'سياسة الدفع',
        };
    }

    public function getType(): string
    {
        return match ($this) {
            self::APP_NAME => 'string',
            self::APP_ICON, self::APP_LOGO => 'image',
            self::MIN_TOTAL_ORDER => 'float',
            self::RATING_POINTS_PERCENT => 'float',
            self::STOP_SELLING => 'boolean',
            self::ORDER_RECEIPT_FOOTER => 'text',
            self::WHATSAPP_SERVER_ENDPOINT => 'string',
            self::SUPPORT_PHONE => 'string',
            self::SUPPORT_EMAIL => 'string',
            self::SUPPORT_ADDRESS => 'text',
            self::SUPPORT_HOURS => 'text',
            self::SUPPORT_CHAT_HOURS => 'text',
            self::PRIVACY_POLICY => 'richtext',
            self::SHIPPING_POLICY => 'richtext',
            self::RETURN_POLICY => 'richtext',
            self::PAYMENT_POLICY => 'richtext',
        };
    }

}
