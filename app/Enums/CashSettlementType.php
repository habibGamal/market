<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CashSettlementType: string implements HasColor, HasIcon, HasLabel
{
    case IN = 'in';
    case OUT = 'out';

    public static function toSelectArray(): array
    {
        return [
            self::IN->value => 'داخل',
            self::OUT->value => 'خارج',
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::IN => 'داخل',
            self::OUT => 'خارج',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::IN => 'success',
            self::OUT => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::IN => 'heroicon-o-arrow-down-circle',
            self::OUT => 'heroicon-o-arrow-up-circle',
        };
    }
}
