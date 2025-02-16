<?php

namespace App\Enums;

enum ExpirationUnit: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public static function values(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => __("general.period.{$case->value}"), self::cases())
        );
    }
}
