<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case PREPARING = 'preparing';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::CANCELLED => 'red',
            self::PREPARING => 'blue',
            self::OUT_FOR_DELIVERY => 'orange',
            self::DELIVERED => 'green',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::PREPARING => 'heroicon-o-cog',
            self::OUT_FOR_DELIVERY => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::CANCELLED => 'ملغاة',
            self::PREPARING => 'قيد التحضير',
            self::OUT_FOR_DELIVERY => 'في الطريق',
            self::DELIVERED => 'تم التوصيل',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::CANCELLED->value => self::CANCELLED->getLabel(),
            self::PREPARING->value => self::PREPARING->getLabel(),
            self::OUT_FOR_DELIVERY->value => self::OUT_FOR_DELIVERY->getLabel(),
            self::DELIVERED->value => self::DELIVERED->getLabel(),
        ];
    }
}
