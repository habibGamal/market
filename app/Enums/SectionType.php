<?php

namespace App\Enums;

enum SectionType: string
{
    case VIRTUAL = 'VIRTUAL';
    case REAL = 'REAL';

    public function getLabel(): string
    {
        return match($this) {
            self::VIRTUAL => 'افتراضي',
            self::REAL => 'حقيقي',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
