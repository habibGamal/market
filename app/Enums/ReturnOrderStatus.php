<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReturnOrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case DRIVER_PICKUP = 'driver_pickup';
    case RECEIVED_FROM_CUSTOMER = 'received_from_customer';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::DRIVER_PICKUP => 'blue',
            self::RECEIVED_FROM_CUSTOMER => 'green',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::DRIVER_PICKUP => 'heroicon-o-truck',
            self::RECEIVED_FROM_CUSTOMER => 'heroicon-o-check-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::DRIVER_PICKUP => 'السائق في الطريق للاستلام',
            self::RECEIVED_FROM_CUSTOMER => 'تم الاستلام من العميل',
        };
    }
}
