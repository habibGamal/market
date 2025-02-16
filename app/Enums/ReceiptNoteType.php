<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReceiptNoteType: string implements HasColor, HasIcon, HasLabel
{
    case PURCHASES = 'purchases';
    case RETURN_ORDERS = 'return_orders';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PURCHASES => 'blue',
            self::RETURN_ORDERS => 'red',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PURCHASES => 'heroicon-o-shopping-cart',
            self::RETURN_ORDERS => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PURCHASES => 'مشتريات',
            self::RETURN_ORDERS => 'مرتجعات',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PURCHASES->value => self::PURCHASES->getLabel(),
            self::RETURN_ORDERS->value => self::RETURN_ORDERS->getLabel(),
        ];
    }

}
