<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DriverBalanceTransactionType: string implements HasColor, HasIcon, HasLabel
{
    case DELIVERY = 'delivery';
    case RETURN = 'return';
    case RECEIPT = 'receipt';
    case ADJUSTMENT = 'adjustment';

    public function getColor(): ?string
    {
        return match ($this) {
            self::DELIVERY => 'success',
            self::RETURN => 'warning',
            self::RECEIPT => 'info',
            self::ADJUSTMENT => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DELIVERY => 'heroicon-o-arrow-up',
            self::RETURN => 'heroicon-o-arrow-down',
            self::RECEIPT => 'heroicon-o-banknotes',
            self::ADJUSTMENT => 'heroicon-o-adjustments-horizontal',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DELIVERY => 'تسليم طلب',
            self::RETURN => 'إرجاع أصناف',
            self::RECEIPT => 'سند صرف محاسب',
            self::ADJUSTMENT => 'تعديل يدوي',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::DELIVERY->value => self::DELIVERY->getLabel(),
            self::RETURN->value => self::RETURN->getLabel(),
            self::RECEIPT->value => self::RECEIPT->getLabel(),
            self::ADJUSTMENT->value => self::ADJUSTMENT->getLabel(),
        ];
    }
}
