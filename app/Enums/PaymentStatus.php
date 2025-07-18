<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case PAID = 'paid';
    case PARTIAL_PAID = 'partial_paid';
    case UNPAID = 'unpaid';

    public static function toSelectArray(): array
    {
        return [
            self::PAID->value => 'مدفوع',
            self::PARTIAL_PAID->value => 'مدفوع جزئياً',
            self::UNPAID->value => 'غير مدفوع',
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PAID => 'مدفوع',
            self::PARTIAL_PAID => 'مدفوع جزئياً',
            self::UNPAID => 'غير مدفوع',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PAID => 'success',
            self::PARTIAL_PAID => 'warning',
            self::UNPAID => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PAID => 'heroicon-o-check-circle',
            self::PARTIAL_PAID => 'heroicon-o-exclamation-triangle',
            self::UNPAID => 'heroicon-o-x-circle',
        };
    }
}
