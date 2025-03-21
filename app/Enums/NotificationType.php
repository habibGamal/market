<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NotificationType: string implements HasLabel
{
    case GENERAL = 'general';

    case PROMOTION = 'promotion';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::GENERAL => 'عام',
            self::PROMOTION => 'عروض وتخفيضات',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::GENERAL->value => self::GENERAL->getLabel(),
            self::PROMOTION->value => self::PROMOTION->getLabel(),
        ];
    }
}
