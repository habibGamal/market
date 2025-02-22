<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DriverStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case RECEIVED = 'received';
    case DONE = 'done';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::RECEIVED => 'blue',
            self::DONE => 'green',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::RECEIVED => 'heroicon-o-inbox-arrow-down',
            self::DONE => 'heroicon-o-check-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::RECEIVED => 'تم الاستلام من المخزن',
            self::DONE => 'مكتمل',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::RECEIVED->value => self::RECEIVED->getLabel(),
            self::DONE->value => self::DONE->getLabel(),
        ];
    }
}
