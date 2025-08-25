<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CashSettlementStatus: string implements HasColor, HasIcon, HasLabel
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';

    public static function toSelectArray(): array
    {
        return [
            self::PAID->value => 'مدفوع',
            self::UNPAID->value => 'غير مدفوع',
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PAID => 'مدفوع',
            self::UNPAID => 'غير مدفوع',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PAID => 'success',
            self::UNPAID => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PAID => 'heroicon-o-check-circle',
            self::UNPAID => 'heroicon-o-x-circle',
        };
    }
}
