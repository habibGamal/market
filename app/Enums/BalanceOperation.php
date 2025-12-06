<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BalanceOperation: string implements HasColor, HasLabel
{
    case INCREMENT = 'increment';
    case DECREMENT = 'decrement';

    public function getColor(): ?string
    {
        return match ($this) {
            self::INCREMENT => 'success',
            self::DECREMENT => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INCREMENT => 'زيادة',
            self::DECREMENT => 'خصم',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::INCREMENT->value => self::INCREMENT->getLabel(),
            self::DECREMENT->value => self::DECREMENT->getLabel(),
        ];
    }
}
