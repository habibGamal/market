<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SectionLocation: string implements HasColor, HasIcon, HasLabel
{
    case HOME = 'HOME';
    case HOT_DEALS = 'HOT_DEALS';

    public function getColor(): ?string
    {
        return match ($this) {
            self::HOME => 'blue',
            self::HOT_DEALS => 'orange',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::HOME => 'heroicon-o-home',
            self::HOT_DEALS => 'heroicon-o-fire',
        };
    }

    public function getLabel(): ?string
    {
        return match($this) {
            self::HOME => 'الرئيسية',
            self::HOT_DEALS => 'العروض المميزة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
